<?php

/**
 * Settlement
 *
 * settlement settles fund distribution. this can be CB, driver, or restaurant
 *
 */

class Crunchbutton_Settlement extends Cana_Model {

	public function __construct( $filters = [] ) {

		$this->restaurants = self::restaurants($filters);

		foreach ($this->restaurants as $restaurant) {
			$restaurant->_payableOrders = $restaurant->payableOrders( $filters );
			$restaurant->payment_data = $this->processOrders( $restaurant->_payableOrders );
		}
	}

	// get restaurants that we need to pay
	public static function restaurants($filters = []) {
		$q = 'SELECT restaurant.*, MAX(p.date) AS last_pay, p.id_restaurant AS p_id_rest
					FROM restaurant
						LEFT OUTER JOIN ( SELECT id_restaurant, `date` FROM `payment` ) AS p using(id_restaurant)
						INNER JOIN restaurant_payment_type rpt ON rpt.id_restaurant = restaurant.id_restaurant
					WHERE active=1';
		if ($filters['payment_method']) {
			 $q .= ' AND `rpt.payment_method`="'.$filters['payment_method'].'" ';
		}
		$q .= ' AND restaurant.id_restaurant
						GROUP BY id_restaurant
						 ORDER BY id_restaurant ASC';
						 // ORDER BY (CASE WHEN p_id_rest IS NULL THEN 1 ELSE 0 END) ASC,
		return Restaurant::q($q);
	}


	// this method receives the restaurant orders and run the math
	public function processOrders( $orders ){

		// start all with 0
		$pay = [ 'card_subtotal' => 0, 'tax' => 0, 'delivery_fee' => 0, 'tip' => 0, 'customer_fee' => 0, 'markup' => 0, 'credit_charge' => 0, 'restaurant_fee' => 0, 'promo_gift_card' => 0, 'apology_gift_card' => 0, 'order_payment' => 0, 'cash_subtotal' => 0 ];

		foreach ( $orders as $order ) {
			$arr = $this->orderExtractVariables( $order );
			$pay[ 'card_subtotal' ] += $this->orderCardSubtotalPayment( $arr );
			$pay[ 'tax' ] += $this->orderTaxPayment( $arr );
			$pay[ 'delivery_fee' ] += $this->orderDeliveryFeePayment( $arr );
			$pay[ 'tip' ] += $this->orderTipPayment( $arr );
			$pay[ 'customer_fee' ] += $this->orderCustomerFeePayment( $arr );
			$pay[ 'markup' ] += $this->orderMarkupPayment( $arr );
			$pay[ 'credit_charge' ] += $this->orderCreditChargePayment( $arr );
			$pay[ 'restaurant_fee' ] += $this->orderRestaurantFeePayment( $arr );
			$pay[ 'promo_gift_card' ] += $this->orderPromoGiftCardPayment( $arr );
			$pay[ 'apology_gift_card' ] += $this->orderApologyGiftCardPayment( $arr );
			$pay[ 'order_payment' ] += $this->orderRestaurantOrderPayment( $arr );
			$pay[ 'cash_subtotal' ] += $this->orderCashSubtotalPayment( $arr );
			$formal_relationship = $arr[ 'formal_relationship' ];
		}

		// sum
		$total_due = 	$pay[ 'card_subtotal' ] +
									$pay[ 'tax' ] +
									$pay[ 'delivery_fee' ] +
									$pay[ 'tip' ] +
									$pay[ 'customer_fee' ] +
									$pay[ 'markup' ] +
									$pay[ 'credit_charge' ] +
									$pay[ 'restaurant_fee' ] +
									$pay[ 'promo_gift_card' ] +
									$pay[ 'apology_gift_card' ];
		$pay[ 'total_due' ] = ( max( $total_due, 0 ) ) * $formal_relationship;
		return $pay;
	}

	// This method calculates the "base" charge for credit orders using the formula
	// that Balanced and Stripe use.
	public function orderBaseCreditCharge( $arr ){
		return ( 0.3 + 0.029 * $arr[ 'total_charged' ] ) * $arr[ 'credit' ];
	}

	// This method calculates the base fee charged to the restaurant.
	// It applies the Restaurant Fee % to the subtotal.
	// It also applies the Fee % to the Delivery Fee and Tax, unless excplicitly excepted,
	// or unless the restaurant has 3rd-party delivery.
	public function orderBaseRestaurantFee( $arr ){
		return ( $arr[ 'restaurant_fee_percent' ] * ( $arr[ 'subtotal' ] + ( 1 - $arr[ 'delivery_service' ] ) * ( 1 - $arr[ 'just_fee_on_subtotal' ] ) * ( $arr[ 'tip' ] + $arr[ 'delivery_fee' ] ) ) ) / 100;
	}

	// For the subtotal, we pay this to the restaurant for credit orders.
	public function orderCardSubtotalPayment( $arr ){
		return $arr[ 'subtotal' ] * $arr[ 'credit' ];
	}

	// Tax is also paid to the restaurant for credit orders.
	public function orderTaxPayment( $arr ){
		return $arr[ 'tax' ] * $arr[ 'credit' ];
	}

	// Delivery fee is paid to the restaurant for credit orders,
	// unless we do delivery ourselves.
	public function orderDeliveryFeePayment( $arr ){
		return $arr[ 'delivery_fee' ] * $arr[ 'credit' ] * ( 1 - $arr[ 'delivery_service' ] );
	}

	// Tip  is paid to the restaurant for credit orders
	// unless we do delivery ourselves.
	public function orderTipPayment( $arr ){
		return $arr[ 'tip' ] * $arr[ 'credit' ] * ( 1 - $arr[ 'delivery_service' ] );
	}

	// We charge the restaurant when they collected our markup from cash orders,
	// except for 3rd-party delivery, when this must be collected from the driver.
	public function orderCustomerFeePayment( $arr ){
		return - ( $arr[ 'service_fee' ] * $arr[ 'cash' ] * ( 1 - $arr[ 'delivery_service' ] ) );
	}

	// We charge the restaurant when they collected our markup from cash orders,
	// except for 3rd-party delivery, when this must be collected from the driver.
	public function orderMarkupPayment( $arr ){
		return - ( $arr[ 'delivery_service_markup_value' ] * $arr[ 'cash' ] * ( 1 - $arr[ 'delivery_service' ] ) );
	}

	// We charge the restaurant our Credit Charge from Balanced except
	// when explicitly agreed otherwise.
	public function orderCreditChargePayment( $arr ){
		return - ( $this->orderBaseCreditCharge( $arr ) * $arr[ 'pay_credit_charge' ] );
	}

	// We charge the restaurant our contracted fee in all cases.
	public function orderRestaurantFeePayment( $arr ){
		return - ( $this->orderBaseRestaurantFee( $arr ) );
	}

	// We charge the restaurants for Promo gift cards up to their Promo maximum.
	// This is set to 0 at the moment because we told them it would only be for new users,
	// and lots of people are using gift cards again and again.
	// When this is fixed, I'll get rid of the "*0" in the formula.
	public function orderPromoGiftCardPayment( $arr ){
		return - ( min( [ $arr[ 'gift_card_paid_by_promotional' ], $arr[ 'promotion_maximum' ] ] ) * $arr[ 'credit' ] ) * 0;
	}

	// We charge restaurants for apology credits up to their apology credit maximum.
	public function orderApologyGiftCardPayment( $arr ){
		return - ( min( [ $arr[ 'gift_card_paid_by_restaurant' ], $arr[ 'max_apology_credit' ] ] ) * $arr[ 'credit' ] );
	}

	// Drivers are paid the subtotal for credit orders when we have no formal
	// relationship with the restaurant and they had to buy the food themselves.
	public function orderSubtotalDriveryPay( $arr ){
		return $arr[ 'subtotal' ] * $arr[ 'credit' ] * ( 1 - $arr[ 'formal_relationship' ] ) * ( 1 - $arr[ 'paid_with_cb_card' ] ) * $arr[ 'delivery_service' ];
	}

	// Drivers are paid the tax for credit orders when we have no formal
	// relationship with the restaurant and they had to buy the food themselves.
	public function orderTaxDriverPay( $arr ){
		return $arr[ 'tax' ] * $arr[ 'credit' ] * ( 1 - $arr[ 'formal_relationship' ] ) * ( 1 - $arr[ 'paid_with_cb_card' ] ) * $arr[ 'delivery_service' ];
	}

	// Drivers are paid the whole delivery fee from credit orders.
	public function orderDeliveryFeeDriverPay( $arr ){
		return $arr[ 'delivery_fee' ] * $arr[ 'credit' ] * $arr[ 'delivery_service' ];
	}

	// Drivers are paid the whole tip from credit orders.
	public function orderTipDriverPay( $arr ){
		return $arr[ 'tip' ] * $arr[ 'credit' ];
	}

	// Drivers must pay us back our markup they collected in cash.
	public function orderCustomerFeeDriverPay( $arr ){
		return - ( $arr[ 'service_fee' ] * $arr[ 'cash' ] );
	}

	// Drivers must pay us back our markup they collected in cash.
	public function orderMarkupDriverPay( $arr ){
		return - ( $arr[ 'delivery_service_markup_value' ] * $arr[ 'cash' ] );
	}

	// Drivers are not charged credit card fee, or gift card charge, for their orders.
	// We eat this cost, or assume it is taken into account when setting our markup.
	public function orderCreditChargeDriverPay( $arr ){
		return 0;
	}

	// Drivers are not charged credit card fee, or gift card charge, for their orders.
	// We eat this cost, or assume it is taken into account when setting our markup.
	public function orderRestaurantFeeDriverPay( $arr ){
		return 0;
	}

	// Drivers are not charged credit card fee, or gift card charge, for their orders.
	// We eat this cost, or assume it is taken into account when setting our markup.
	public function orderGiftCardDriverPay( $arr ){
		return 0;
	}

	// Sum the amount we have to pay
	public function orderRestaurantOrderPayment( $arr ){
		return 	$this->orderCardSubtotalPayment( $arr ) +
						$this->orderTaxPayment( $arr ) +
						$this->orderDeliveryFeePayment( $arr ) +
						$this->orderTipPayment( $arr ) +
						$this->orderCustomerFeePayment( $arr ) +
						$this->orderMarkupPayment( $arr );
	}

	public function orderCashSubtotalPayment( $arr ){
		return 	( $arr[ 'subtotal' ] +
							$arr[ 'tax' ] +
							( $arr[ 'tip' ] + $arr[ 'delivery_fee' ] ) *
							( 1 - $arr[ 'delivery_service' ] ) ) * $arr[ 'cash' ];
	}

	public function orderTotalFee( $arr ){
		return $arr[ 'customer_fee_percent' ] + $arr[ 'restaurant_fee_percent' ];
	}

	public function orderExtractVariables( $order ){
		// Get all variables
		$values = [];
		$values[ 'subtotal' ] = $order->subtotal();
		$values[ 'tax' ] = $order->tax();
		$values[ 'tip' ] = $order->tip();
		$values[ 'delivery_fee' ] = $order->deliveryFee();
		$values[ 'service_fee' ] = $order->serviceFee();
		$values[ 'customer_fee' ] = $order->customer_fee();
		$values[ 'customer_fee_percent' ] = $order->restaurant()->fee_customer;
		$values[ 'restaurant_fee_percent' ] = $order->restaurant_fee_percent();
		$values[ 'delivery_service_markup' ] = $order->delivery_service_markup;
		$values[ 'delivery_service_markup_value' ] = $order->delivery_service_markup_value;
		$values[ 'id_admin' ] = $order->getDeliveryDriver()->id_admin; // driver

		$values[ 'gift_card_total' ] = $order->chargedByCredit();
		$values[ 'gift_card_paid_by_crunchbutton' ] = Crunchbutton_Credit::creditByOrderPaidBy( $order->id_order, Crunchbutton_Credit::PAID_BY_CRUNCHBUTTON );
		$values[ 'gift_card_paid_by_restaurant' ] = Crunchbutton_Credit::creditByOrderPaidBy( $order->id_order, Crunchbutton_Credit::PAID_BY_RESTAURANT );
		$values[ 'gift_card_paid_by_promotional' ] = Crunchbutton_Credit::creditByOrderPaidBy( $order->id_order, Crunchbutton_Credit::PAID_BY_PROMOTIONAL );
		$values[ 'gift_card_paid_by_other_restaurant' ] = Crunchbutton_Credit::creditByOrderPaidBy( $order->id_order, Crunchbutton_Credit::PAID_BY_OTHER_RESTAURANT );

		$values[ 'total_charged' ] = $order->charged();
		$values[ 'promotion_maximum' ] = $order->restaurant()->payment_type()->max_pay_promotion;
		$values[ 'max_apology_credit' ] = $order->restaurant()->payment_type()->max_apology_credit;

		$values[ 'credit' ] = ( $order->pay_type == Crunchbutton_Order::PAY_TYPE_CREDIT_CARD ) ? 1 : 0;
		$values[ 'cash' ] = ( $order->pay_type == Crunchbutton_Order::PAY_TYPE_CASH ) ? 1 : 0;
		$values[ 'charge_credit_fee' ] = ( $order->restaurant()->charge_credit_fee > 0 )  ? 1 : 0;
		$values[ 'pay_credit_charge' ] = ( $order->restaurant()->charge_credit_fee > 0 )  ? 1 : 0;
		$values[ 'pay_promotion' ] = ( $order->restaurant()->payment_type()->max_pay_promotion > 0 ) ? 1 : 0;
		$values[ 'just_fee_on_subtotal' ] = ( $order->restaurant()->fee_on_subtotal > 0 ) ? 1: 0;
		$values[ 'delivery_service' ] = ( $order->delivery_service > 0 ) ? 1: 0;
		$values[ 'formal_relationship' ] = ( $order->restaurant()->formal_relationship > 0 ) ? 1: 0;
		$values[ 'paid_with_cb_card' ] = ( $order->paid_with_cb_card > 0 ) ? 1: 0;

		// convert all to float -> mysql returns some values as string
		foreach( $values as $key => $val ){
			$values[ $key ] = floatval( $val );
		}
		return $values;
	}
}




/*******************************************

	// this method receives the restaurant orders and run the math
	public function payableOrders( $orders ){

		foreach ($orders as $order) {


			// @note: i dont know what this is at all or why its a fixed 85% -devin
			if ( Crunchbutton_Credit::creditByOrderPaidBy( $order->id_order, Crunchbutton_Credit::PAID_BY_PROMOTIONAL ) ) {
				$order->_display_price *= 0.85;
				$order->_display_final_price *= 0.85;
			} else {
				$order->_display_price = $order->price;
				$order->_display_final_price = $order->final_price;
			}

			if ($restaurant->charge_credit_fee == '0') {
				$order->_cc_fee = 0;
			} else {
				$order->_cc_fee = $order->pay_type == 'card' ? .3 + .029 * $order->_display_final_price : 0;
			}
			$order->_cb_fee = $order->cbFee(); // return ($this->restaurant()->fee_restaurant) * ($this->price) / 100;

			if ($order->pay_type == 'card') {
				$order->restaurant()->_settlement_card += $order->_display_final_price;
			} else {
				$order->restaurant()->_settlement_cash += $order->_display_final_price;
			}

			$order->restaurant()->_settlement_cc_fees += $order->_cc_fee;
			$order->restaurant()->_settlement_cb_fees += $order->_cb_fee;

			// @todo: determine if a driver picked this up and add them to a payment list


		}
		return $orders;
	}

********************************************/