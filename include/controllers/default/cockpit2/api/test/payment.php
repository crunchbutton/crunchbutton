<?php

class Controller_Api_Test_Payment extends Crunchbutton_Controller_RestAccount {

	public function init() {

		// script to fix #4020

		die('remove this line');

		$out = [ 'ok' => [], 'nope' => [] ];

		// Get the payments schedule with error
		$payments_schedule = Cockpit_Payment_Schedule::q( 'SELECT * FROM payment_schedule WHERE status = "' . Cockpit_Payment_Schedule::STATUS_PROCESSING . '" ORDER BY id_payment_schedule ASC' );
		foreach( $payments_schedule as $payment_schedule ){
			$nope = false;
			// Get the payment of the schedule
			$payment = Crunchbutton_Payment::q( 'SELECT * FROM payment WHERE id_driver = "' . $payment_schedule->id_driver . '" AND ROUND( amount, 2 ) = ' . $payment_schedule->amount );
			if( $payment->id_payment ){
				// Check if the payment is already related with another payment schedule
				$chedule = Cockpit_Payment_Schedule::q( 'SELECT * FROM payment_schedule WHERE id_payment = ' . $payment->id_payment );
				if( $chedule->count() == 0 ){
					$payment_schedule->id_payment = $payment->id_payment;
					$payment_schedule->status = Cockpit_Payment_Schedule::STATUS_DONE;
					$payment_schedule->log = 'Payment finished';
					$payment_schedule->status_date = $payment->date;
					$payment_schedule->save();
					$out[ 'ok' ][] = $payment_schedule->id_payment_schedule;
					$nope = true;
				}
			}
			if( !$nope ){
				$payment_schedule->id_payment = $payment->id_payment;
				$payment_schedule->status = Cockpit_Payment_Schedule::STATUS_ERROR;
				$payment_schedule->log = 'Payment error';
				$payment_schedule->status_date = date( 'YmdHis' );
				$payment_schedule->save();
				$out[ 'nope' ][] = $payment_schedule->id_payment_schedule;
			}
		}
		echo json_encode( $out );exit;
	}
}