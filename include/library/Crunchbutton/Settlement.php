<?php

/**
 * Settlement
 *
 * settlement settles fund distribution. this can be CB, driver, or restaurant
 *
 */

class Crunchbutton_Settlement extends Cana_Model {

	const DEFAULT_NOTES = 'Crunchbutton Orders';

	public function __construct( $filters = [] ) {
		$this->filters = $filters;
	}

	public function startRestaurant(){

		$this->restaurants = self::restaurants( $this->filters );

		foreach ( $this->restaurants as $restaurant ) {
			$restaurant->_payableOrders = $restaurant->payableOrders( $this->filters );
			$orders = [];
			foreach( $restaurant->_payableOrders as $order ){
				$orders[] = $this->orderExtractVariables( $order );
			}
			$restaurant->payment_data = $this->restaurantsProcessOrders( $orders );
		}
		return $this->restaurants;
	}

	public function startDriver(){
		$this->restaurants = self::restaurants( $this->filters );
		$orders = [];
		foreach ( $this->restaurants as $restaurant ) {
			$restaurant->_payableOrders = $restaurant->payableOrders( $this->filters );
			foreach( $restaurant->_payableOrders as $order ){
				$orders[] = $this->orderExtractVariables( $order );
			}
		}
		return $this->driversProcessOrders( $orders );
	}

	// get restaurants that we need to pay
	public static function restaurants($filters = []) {
		$q = 'SELECT restaurant.*, MAX(p.date) AS last_pay, p.id_restaurant AS p_id_rest
					FROM restaurant
						LEFT OUTER JOIN ( SELECT id_restaurant, `date` FROM `payment` ) AS p using(id_restaurant)
						INNER JOIN restaurant_payment_type rpt ON rpt.id_restaurant = restaurant.id_restaurant
					WHERE active=1 ';
		if ($filters['payment_method']) {
			 $q .= ' AND rpt.payment_method = "'.$filters['payment_method']. '" ';
		} else {
			$q .= ' AND ( rpt.payment_method = "check" OR rpt.payment_method = "deposit" ) ';
		}
		if( $filters[ 'id_restaurant' ] ){
			$q .= ' AND restaurant.id_restaurant = "' . $filters[ 'id_restaurant' ] . '"';
		}
		$q .= ' AND restaurant.id_restaurant
						GROUP BY restaurant.id_restaurant
						 ORDER BY (CASE WHEN p_id_rest IS NULL THEN 1 ELSE 0 END) ASC';
		return Restaurant::q( $q );
	}


	// this method receives the restaurant orders and run the math
	public function restaurantsProcessOrders( $orders, $recalculatePaidOrders = false ){
		// start all with 0
		$pay = [ 'card_subtotal' => 0, 'cash_reimburse' => 0, 'tax' => 0, 'delivery_fee' => 0, 'tip' => 0, 'customer_fee' => 0, 'markup' => 0, 'credit_charge' => 0, 'restaurant_fee' => 0, 'promo_gift_card' => 0, 'apology_gift_card' => 0, 'order_payment' => 0, 'cash_subtotal' => 0 ];
		foreach ( $orders as $order ) {
			if( $order ){
				// Pay if Refunded
				if( ( $order[ 'refunded' ] == 1 && $order[ 'pay_if_refunded' ] == 0 ) ){
					continue;
				}
				if( $order[ 'restaurant_paid' ] && !$recalculatePaidOrders ){
					continue;
				}
				$pay[ 'card_subtotal' ] += $this->orderCardSubtotalPayment( $order );
				$pay[ 'tax' ] += $this->orderTaxPayment( $order );
				$pay[ 'delivery_fee' ] += $this->orderDeliveryFeePayment( $order );
				$pay[ 'tip' ] += $this->orderTipPayment( $order );
				$pay[ 'customer_fee' ] += $this->orderCustomerFeePayment( $order );
				$pay[ 'markup' ] += $this->orderMarkupPayment( $order );
				$pay[ 'credit_charge' ] += $this->orderCreditChargePayment( $order );
				$pay[ 'restaurant_fee' ] += $this->orderRestaurantFeePayment( $order );
				$pay[ 'promo_gift_card' ] += $this->orderPromoGiftCardPayment( $order );
				$pay[ 'apology_gift_card' ] += $this->orderApologyGiftCardPayment( $order );
				$pay[ 'order_payment' ] += $this->orderRestaurantOrderPayment( $order );
				$pay[ 'cash_subtotal' ] += $this->orderCashSubtotalPayment( $order );
				$pay[ 'cash_reimburse' ] += $this->orderReimburseCashOrder( $order );
				$pay[ 'formal_relationship' ] = $order[ 'formal_relationship' ];
			}
		}
		foreach ( $pay as $key => $val ) {
			// $pay[ $key ] = Util::round_up( number_format( $val, 3 ), 2 );
		}
		// sum
		$pay[ 'total_due' ] = $this->orderCalculateTotalDue( $pay );
		return $pay;
	}

	// this method receives the restaurant orders and run the math
	public function driversProcessOrders( $orders ){
		$pay = [];
		foreach ( $orders as $order ) {
			if( $order && $order[ 'id_admin' ] ){
				$driver = $order[ 'id_admin' ];
				if( !$pay[ $driver ] ){
					$pay[ $driver ] = [ 'subtotal' => 0, 'tax' => 0, 'delivery_fee' => 0, 'tip' => 0, 'customer_fee' => 0, 'markup' => 0, 'credit_charge' => 0, 'restaurant_fee' => 0, 'gift_card' => 0, 'orders' => [] ];
					$pay[ $driver ][ 'id_admin' ] = $driver;
					$pay[ $driver ][ 'name' ] = $order[ 'driver' ];
				}
				$pay[ $driver ][ 'orders' ][] = $order;
				$pay[ $driver ][ 'subtotal' ] += $this->orderSubtotalDriveryPay( $order );
				$pay[ $driver ][ 'tax' ] += $this->orderTaxDriverPay( $order );
				$pay[ $driver ][ 'delivery_fee' ] += $this->orderDeliveryFeeDriverPay( $order );
				$pay[ $driver ][ 'tip' ] += $this->orderTipDriverPay( $order );
				$pay[ $driver ][ 'customer_fee' ] += $this->orderCustomerFeeDriverPay( $order );
				$pay[ $driver ][ 'markup' ] += $this->orderMarkupDriverPay( $order );
				$pay[ $driver ][ 'credit_charge' ] += $this->orderCreditChargeDriverPay( $order );
				$pay[ $driver ][ 'restaurant_fee' ] += $this->orderRestaurantFeeDriverPay( $order );
				$pay[ $driver ][ 'gift_card' ] += $this->orderGiftCardDriverPay( $order );
			}
		}

		foreach( $pay as $key => $val ){
			$pay[ $key ][ 'total_due' ] = $this->orderCalculateTotalDueDriver( $pay[ $key ] );
		}

		usort( $pay, function( $a, $b ) {
			return $a[ 'name'] > $b[ 'name' ];
		});

		return $pay;
	}

	public function orderCalculateTotalDueDriver( $pay ){
		$total_due = 	$pay[ 'subtotal' ] +
									$pay[ 'tax' ] +
									$pay[ 'delivery_fee' ] +
									$pay[ 'tip' ] +
									$pay[ 'customer_fee' ] +
									$pay[ 'markup' ] +
									$pay[ 'credit_charge' ] +
									$pay[ 'restaurant_fee' ] +
									$pay[ 'gift_card' ];
		return $total_due;
	}

	public function orderCalculateTotalDue( $pay ){
		$total_due = 	$pay[ 'card_subtotal' ] +
									$pay[ 'tax' ] +
									$pay[ 'delivery_fee' ] +
									$pay[ 'tip' ] +
									$pay[ 'customer_fee' ] +
									$pay[ 'markup' ] +
									$pay[ 'credit_charge' ] +
									$pay[ 'restaurant_fee' ] +
									$pay[ 'promo_gift_card' ] +
									$pay[ 'apology_gift_card' ] +
									$pay[ 'cash_reimburse' ];
		return ( max( $total_due, 0 ) ) * $pay[ 'formal_relationship' ];
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

	// For cash orders: reimburse just the cost of the food, so Subtotal + Tax
	public function orderReimburseCashOrder( $arr ){
		return ( $arr[ 'subtotal' ] + $arr[ 'tax' ] ) * $arr[ 'cash' ] * $arr[ 'reimburse_cash_order' ];
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
		$values[ 'final_price_plus_delivery_markup' ] = $order->final_price_plus_delivery_markup;
		$values[ 'delivery_fee' ] = $order->deliveryFee();
		$values[ 'service_fee' ] = $order->serviceFee();
		$values[ 'customer_fee' ] = $order->customer_fee();
		$values[ 'customer_fee_percent' ] = $order->restaurant()->fee_customer;
		$values[ 'restaurant_fee_percent' ] = $order->restaurant_fee_percent();
		$values[ 'delivery_service_markup' ] = $order->delivery_service_markup;
		$values[ 'delivery_service_markup_value' ] = $order->delivery_service_markup_value;
		$values[ 'id_admin' ] = $order->getDeliveryDriver()->id_admin; // driver
		$values[ 'id_order' ] = $order->id_order; // driver

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
		$values[ 'refunded' ] = ( $order->refunded > 0 ) ? 1: 0;
		$values[ 'pay_if_refunded' ] = ( $order->pay_if_refunded > 0 ) ? 1: 0;
		$values[ 'reimburse_cash_order' ] = ( $order->reimburse_cash_order > 0 ) ? 1: 0;

		$values[ 'restaurant_paid' ] = Cockpit_Payment_Schedule_Order::checkOrderWasPaidRestaurant( $order->id_order );
		if( !$values[ 'restaurant_paid' ] ){
			$values[ 'restaurant_paid' ] = Crunchbutton_Order_Transaction::checkOrderWasPaidRestaurant( $order->id_order );
		}

		// convert all to float -> mysql returns some values as string
		foreach( $values as $key => $val ){
			$values[ $key ] = floatval( $val );
		}

		if( $values[ 'id_admin' ] ){
			$admin = Admin::o( $values[ 'id_admin' ] );
			$values[ 'driver' ] = $admin->name;
		} else {
			$values[ 'driver' ] = '';
		}

		$values[ 'name' ] = $order->name;
		$values[ 'pay_type' ] = $order->pay_type;
		$values[ 'restaurant' ] = $order->restaurant()->name;
		$values[ 'date' ] = $order->date()->format( 'M jS Y g:i:s A' );
		$values[ 'short_date' ] = $order->date()->format( 'M jS Y' );

		return $values;
	}

	public function scheduleRestaurantPayment( $id_restaurants ){
		$restaurants = $this->startRestaurant();
		foreach ( $restaurants as $_restaurant ) {
			// todo: build a better way to filter - this way is very ugly
			if( !$id_restaurants[ $_restaurant->id_restaurant ] ){
				continue;
			}
			$notes = $id_restaurants[ $_restaurant->id_restaurant ];
			$id_restaurant = $_restaurant->id_restaurant;
			$payment_data = $_restaurant->payment_data;

			// check if it has any order to be paid
			$shouldSchedule = ( $payment_data[ 'total_due' ] > 0 ) ? true : false;

			foreach ( $_restaurant->_payableOrders as $order ) {
				$alreadyPaid = Cockpit_Payment_Schedule_Order::checkOrderWasPaidRestaurant( $order->id_order );
				if( !$alreadyPaid ){
					$shouldSchedule = true;
				}
			}
			if( $shouldSchedule ){
				// schedule it
				$schedule = new Cockpit_Payment_Schedule;
				$schedule->id_restaurant = $_restaurant->id_restaurant;
				$schedule->date = date( 'Y-m-d H:i:s' );
				$schedule->amount = max( $payment_data[ 'total_due' ], 0 );
				$schedule->payment_method = $_restaurant->payment_type()->payment_method;
				$schedule->type = Cockpit_Payment_Schedule::TYPE_RESTAURANT;
				$schedule->status = Cockpit_Payment_Schedule::STATUS_SCHEDULED;
				$schedule->notes = $notes;
				$schedule->id_admin = c::user()->id_admin;
				$schedule->save();
				$id_payment_schedule = $schedule->id_payment_schedule;
				// save the orders
				foreach ( $_restaurant->_payableOrders as $order ) {
					$total_due = $this->restaurantsProcessOrders( [ $this->orderExtractVariables( $order ) ] );
					$schedule_order = new Cockpit_Payment_Schedule_Order;
					$schedule_order->id_payment_schedule = $id_payment_schedule;
					$schedule_order->id_order = $order->id_order;
					$schedule_order->amount = max( $total_due[ 'total_due' ], 0 );
					$schedule_order->save();
				}
			}
		}
		$settlement = new Crunchbutton_Settlement;
		// Cana::timeout(function() use( $settlement ) {
			$settlement->doRestaurantPayments();
		// } );
	}

	public function doRestaurantPayments( $id_payment_schedule = false ){
		if( $id_payment_schedule ){
			return $this->payRestaurant( $id_payment_schedule );
		} else {
			$schedule = new Cockpit_Payment_Schedule;
			$lastDate = $schedule->lastRestaurantStatusDate();
			$schedules = $schedule->restaurantSchedulesFromDate( $lastDate );
			foreach( $schedules as $_schedule ){
				$this->payRestaurant( $_schedule->id_payment_schedule );
			}
		}
	}

	public function restaurantSummaryByIdPayment( $id_payment ){
		$schedule = Cockpit_Payment_Schedule::q( 'SELECT * FROM payment_schedule WHERE id_payment = "' . $id_payment . '"' );
		if( $schedule->id_payment_schedule ){
			return $this->restaurantSummary( $schedule->id_payment_schedule );
		} else {
			return false;
		}
	}

	public function restaurantSummary( $id_payment_schedule ){
		$schedule = Cockpit_Payment_Schedule::o( $id_payment_schedule );
		if( $schedule->id_payment_schedule && $schedule->type == Cockpit_Payment_Schedule::TYPE_RESTAURANT ){
			$settlement = new Settlement;
			$summary = $schedule->exports();
			$summary[ 'restaurant' ] = $schedule->restaurant()->name;
			$summary[ 'summary_method' ] = $schedule->restaurant()->payment_type()->summary_method;
			$summary[ 'summary_email' ] = $schedule->restaurant()->payment_type()->summary_email;
			$summary[ 'summary_fax' ] = $schedule->restaurant()->payment_type()->summary_fax;
			$summary[ 'restaurant' ] = $schedule->restaurant()->name;
			$payment = $schedule->payment();
			if( $payment->id_payment ){
				$summary[ 'balanced_id' ] = $payment->balanced_id;
				$summary[ 'stripe_id' ] = $payment->stripe_id;
				$summary[ 'summary_sent_date' ] = $payment->summary_sent_date()->format( 'M jS Y g:i:s A T' );
				$summary[ 'payment_date' ] = $payment->date()->format( 'M jS Y g:i:s A T' );
			}
			$orders = $schedule->orders();
			$_orders = [];
			$summary[ 'orders_cash' ] = 0;
			$summary[ 'orders_card' ] = 0;
			$summary[ 'orders' ] = [ 'card' => [], 'cash' => [] ];
			foreach( $orders as $order ){
				$_order = $order->order();
				if( $_order->id_order ){
					$variables = $settlement->orderExtractVariables( $_order );
					$type = $variables[ 'cash' ] ? 'cash' : 'card';
					if( $type == 'cash' || ( $type == 'card' && $order->amount > 0 ) ){
						if( $type == 'card' ){
							$summary[ 'orders_card' ]++;
						} else {
							$summary[ 'orders_cash' ]++;
						}
						$summary[ 'orders' ][ $type ][] = [ 'id_order' => $variables[ 'id_order' ], 'name' => $variables[ 'name' ], 'total' => $variables[ 'final_price_plus_delivery_markup' ], 'date' => $variables[ 'short_date' ], 'tip' => $variables[ 'tip' ] ];
						$_orders[] = $variables;
					}
				}
			}
			$summary[ 'calcs' ] = $settlement->restaurantsProcessOrders( $_orders, true );
			$summary[ 'admin' ] = [ 'id_admin' => $schedule->id_admin, 'name' => $schedule->admin()->name ];
			return $summary;
		} else {
			return false;
		}
	}

	public function payRestaurant( $id_payment_schedule ){

		$env = c::getEnv();

		$schedule = Cockpit_Payment_Schedule::o( $id_payment_schedule );

		if( $schedule->id_payment_schedule ){

			if( $schedule->status == Cockpit_Payment_Schedule::STATUS_SCHEDULED ||
					$schedule->status == Cockpit_Payment_Schedule::STATUS_ERROR ){

				// Save the processing date
				$schedule->status = Cockpit_Payment_Schedule::STATUS_PROCESSING;
				$schedule->status_date = date( 'Y-m-d H:i:s' );
				$schedule->save();

				// @todo:: payment api stuff here!
				// $p = Payment::credit([
				// 	'id_restaurant' => $r->id_restaurant,
				// 	'amount' => $this->request()['amount'],
				// 	'note' => $this->request()['note'],
				// 	'type' => 'balanced'
				// ]);
				// If some problem happened
				if( false ){

					$message = 'Restaurant Payment error! Restaurant: ' . $schedule->restaurant()->name;
					$message .= "\n". 'id_payment_schedule: ' . $schedule->id_payment_schedule;
					$message .= "\n". 'amount: ' . $schedule->amount;
					$schedule->status = Cockpit_Payment_Schedule::STATUS_ERROR;
					$schedule->status_date = date( 'Y-m-d H:i:s' );
					$schedule->save();
					Crunchbutton_Support::createNewWarning(  [ 'body' => $message ] );

				} else {

					$payment = new Crunchbutton_Payment;
					$payment->id_restaurant = $schedule->id_restaurant;
					$payment->date = date( 'Y-m-d H:i:s' );
					$payment->amount = $schedule->amount;
					$payment->balanced_id = $schedule->balanced_id;
					$payment->stripe_id = $schedule->stripe_id;
					$payment->note = $schedule->notes;
					$payment->env = $env;
					$payment->id_admin = $schedule->id_admin;
					$payment->save();

					$schedule->id_payment = $payment->id_payment;
					$schedule->status = Cockpit_Payment_Schedule::STATUS_DONE;
					$schedule->status_date = date( 'Y-m-d H:i:s' );
					$schedule->save();

					$orders = $schedule->orders();

					foreach (  $orders as $order ) {

						$order_transaction = new Crunchbutton_Order_Transaction;
						$order_transaction->id_order = $order->id_order;
						$order_transaction->amt = $order->amount;
						$order_transaction->date = date( 'Y-m-d H:i:s' );
						$order_transaction->type = Crunchbutton_Order_Transaction::TYPE_PAID_TO_RESTAURANT;
						$order_transaction->source = Crunchbutton_Order_Transaction::SOURCE_CRUNCHBUTTON;
						$order_transaction->id_admin = $payment->id_admin;
						$order_transaction->save();

						$payment_order_transaction = new Cockpit_Payment_Order_Transaction;
						$payment_order_transaction->id_payment = $payment->id_payment;
						$payment_order_transaction->id_order_transaction = $order_transaction->id_order_transaction;
						$payment_order_transaction->save();
					}

					// $this->sendRestaurantPaymentNotification( $payment->id_payment );
					return true;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

 	public function sendRestaurantPaymentNotification( $id_payment ){

		$summary = $this->restaurantSummaryByIdPayment( $id_payment );
		if( !$summary ){
			return false;
		}

		$env = c::getEnv();

		$mail = ( $env == 'live' ? $summary[ 'summary_email' ] : '_EMAIL' );
		$fax = ( $env == 'live' ? $summary[ 'summary_fax' ] : '_PHONE_' );

		$mail = new Crunchbutton_Email_Payment_Summary( [ 'summary' => $summary ] );

		switch ( $summary[ 'summary_method' ] ) {
			case 'email':

				if ( $mail->send() ) {
					$payment = Crunchbutton_Payment::o( $id_payment );
					$payment->summary_sent_date = date('Y-m-d H:i:s');
					$payment->save();
					return true;
				}
				return false;

				break;

			case 'fax':

				$temp = tempnam( '/tmp','fax' );
				file_put_contents( $temp, $mail->message() );
				rename($temp, $temp.'.html');

				$fax = new Phaxio( [ 'to' => $fax, 'file' => $temp.'.html' ] );

				unlink( $temp.'.html' );

				if ( $fax->success ) {
					$payment = Crunchbutton_Payment::o( $id_payment );
					$payment->summary_sent_date = date('Y-m-d H:i:s');
					$payment->save();
					return true;
				}
				return false;

				break;
		}

		// echo $mail->message();
		return false;
	}
}
