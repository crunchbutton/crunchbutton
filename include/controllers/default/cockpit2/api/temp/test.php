<?php
class Controller_api_temp_test extends Crunchbutton_Controller_Rest {
	public function init() {
		$payments = Payment::q('SELECT * FROM payment WHERE stripe_id IS NOT NULL AND payment_date_checked IS NULL LIMIT 100');
		foreach ($payments as $payment) {
			$payment->checkStripeStatus();
		}
		echo 'end';
	}
}