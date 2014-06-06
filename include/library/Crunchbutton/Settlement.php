<?php

/**
 * Settlement
 *
 * settlement settles fund distribution. this can be CB, driver, or restaurant
 *
 */

class Crunchbutton_Settlement extends Cana_Model {

	public function __construct($filters = []) {
		$this->restaurants = self::restaurants($filters);
		foreach ($this->restaurants as $restaurant) {
			$restaurant->_settlement_cash = 0;
			$restaurant->_settlement_card = 0;
			$restaurant->_settlement_cc_fees = 0;
			$restaurant->_settlement_cb_fees = 0;

			$restaurant->_payableOrders = $this->payableOrders( $restaurant->payableOrders($filters) );

			// PER RESTAURANT FEE CALCULATION
			// these figures are NOT correct. there is alot more that needs to be taken into account
			$restaurant->_settlement_total =
				$restaurant->_settlement_cash
				+ $restaurant->_settlement_card
				- $restaurant->_settlement_cc_fees
				- $restaurant->_settlement_cb_fees;
			/*
			 * END PER ORDER FEE CALCULATION
			 ***/
		}
	}

	// get restaurants that we need to pay
	public static function restaurants($filters = []) {
		$q = 'SELECT restaurant.*,
					       MAX(p.date) AS last_pay,
					       p.id_restaurant AS p_id_rest
					FROM restaurant
					LEFT OUTER JOIN
					  (SELECT id_restaurant,
					          `date`
					   FROM `payment`) AS p using(id_restaurant)
					INNER JOIN restaurant_payment_type rpt ON rpt.id_restaurant = restaurant.id_restaurant
					WHERE active=1';
		if ($filters['payment_method']) {
			 $q .= ' AND `rpt.payment_method`="'.$filters['payment_method'].'" ';
		}
		$q .= ' AND restaurant.id_restaurant = 210
						GROUP BY id_restaurant
						ORDER BY (CASE WHEN p_id_rest IS NULL THEN 1 ELSE 0 END) ASC, last_pay ASC';
		return Restaurant::q($q);
	}


	// this method receives the restaurant orders and run the math
	public function payableOrders( $orders ){

		foreach ( $orders as $order ) {

			$order = $this->processOrder( $order );

		}
		return $orders;
	}


	public function processOrder( $order ){
		$subtotal = $order->subtotal();
		echo '<pre>';var_dump( $subtotal );exit();
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