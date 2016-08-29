<?php
// https://cockpit.la/settlement/restaurants/scheduled/55050
class Controller_Api_Script_RelatePayments extends Crunchbutton_Controller_RestAccount {

	public function init() {
		$schedules = Payment_Schedule::q('SELECT * FROM payment_schedule WHERE id_payment IS NULL AND id_restaurant IS NOT NULL AND amount IS NOT NULL AND status = "done" ORDER BY id_payment_schedule DESC');
		foreach ($schedules as $schedule) {
			$payment = Payment::q('SELECT * FROM payment p WHERE id_restaurant = ? AND ROUND(amount, 2) = ? AND id_payment NOT IN (SELECT id_payment FROM payment_schedule WHERE id_restaurant = ? AND id_payment IS NOT NULL)', [$schedule->id_restaurant, $schedule->amount, $schedule->id_restaurant]);
			if($payment->id_payment){
				$schedule->id_payment = $payment->id_payment;
				$schedule->save();
				echo $schedule->id_payment_schedule . ':' . $payment->id_payment . "\n";
			} else {
				die('hard');
			}

		}
	}
}