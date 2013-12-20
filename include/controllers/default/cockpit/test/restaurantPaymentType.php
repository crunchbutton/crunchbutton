<?php
class Controller_test_restaurantPaymentType extends Crunchbutton_Controller_Account {
	public function init() {
		// die( 'remove this die in order to get it working!' );
		$restaurants = Restaurant::q( 'SELECT * FROM restaurant' );
		foreach ( $restaurants as $restaurant ) {
			$payment = new Crunchbutton_Restaurant_Payment_Type();
			$payment->id_restaurant = $restaurant->id_restaurant;
			$payment->payment_method = $restaurant->payment_method;
			$payment->id_restaurant_pay_another_restaurant = $restaurant->id_restaurant_pay_another_restaurant;
			$payment->check_address = $restaurant->check_address;
			$payment->contact_name = $restaurant->contact_name;
			$payment->summary_fax = $restaurant->summary_fax;
			$payment->summary_email = $restaurant->summary_email;
			$payment->summary_frequency = $restaurant->summary_frequency;
			$payment->legal_name_payment = $restaurant->legal_name_payment;
			$payment->summary_method = $restaurant->summary_method;
			$payment->tax_id = $restaurant->tax_id;
			$payment->charge_credit_fee = $restaurant->charge_credit_fee;
			$payment->waive_fee_first_month = $restaurant->waive_fee_first_month;
			$payment->pay_promotions = $restaurant->pay_promotions;
			$payment->pay_apology_credits = $restaurant->pay_apology_credits;
			$payment->max_apology_credit = $restaurant->max_apology_credit;
			$payment->balanced_id = $restaurant->balanced_id;
			$payment->balanced_bank = $restaurant->balanced_bank;
			$payment->save();
		}
		echo 'ok, it is done!';
	}
}