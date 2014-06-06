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
		$q = '
			select
				restaurant.*, max(p.date) as last_pay, p.id_restaurant as p_id_rest
			from restaurant
			left outer join (select id_restaurant, `date` from `payment`) as p using(id_restaurant)
			inner join restaurant_payment_type rpt on rpt.id_restaurant = restaurant.id_restaurant
			where active=1
		';
		if ($filters['payment_method']) {
			 $q .= ' and `rpt.payment_method`="'.$filters['payment_method'].'" ';
		}
		$q .= '
			group by id_restaurant
			order by
				(case when p_id_rest is null then 1 else 0 end) asc,
				last_pay asc
		';

		return Restaurant::q($q);
	}


	// this method receives the restaurant orders and run the math
	public function payableOrders( $orders ){

		foreach ($orders as $order) {

			/***
			 * BEGIN PER ORDER FEE CALCULATION
			 */

			// @note: i dont know what this is at all or why its a fixed 85% -devin
			if (Crunchbutton_Credit::creditByOrderPaidBy($order->id_order, Crunchbutton_Credit::PAID_BY_PROMOTIONAL)) {
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

			/***
			 * BEGIN PER RESTAURANT FEE CALCULATION
			 */

			$order->restaurant()->_settlement_cc_fees += $order->_cc_fee;
			$order->restaurant()->_settlement_cb_fees += $order->_cb_fee;

			// @todo: determine if a driver picked this up and add them to a payment list

			/*
			 * END PER ORDER FEE CALCULATION
			 ***/

		}
		return $orders;
	}

}