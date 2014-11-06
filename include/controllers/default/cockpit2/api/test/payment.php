<?php

class Controller_Api_Test_Payment extends Crunchbutton_Controller_RestAccount {

	public function init() {

		$out = [ 'ok' => [], 'nope' => [] ];

		// $settlement = new Settlement;

		// $payments_schedule = Cockpit_Payment_Schedule::q( 'SELECT ps.* FROM payment_schedule ps INNER JOIN payment p ON p.id_payment = ps.id_payment AND p.balanced_id IS NULL AND ps.status = "' . Cockpit_Payment_Schedule::STATUS_DONE . '" ORDER BY ps.id_payment_schedule DESC' );
		// foreach( $payments_schedule as $payment_schedule ){
		// 	$nope = false;
		// 	// echo '<pre>';var_dump( $payment_schedule );exit();

		// 	// Fix the pay_type null error
		// 	if( !$payment_schedule->pay_type ){
		// 		$payment_schedule->pay_type = Cockpit_Payment_Schedule::PAY_TYPE_PAYMENT;
		// 		$payment_schedule->save();
		// 	}

		// 	if( $payment_schedule->amount ){
		// 		$payment_schedule->id_payment = $payment->id_payment;
		// 		$payment_schedule->status = Cockpit_Payment_Schedule::STATUS_SCHEDULED;
		// 		$payment_schedule->log = 'Schedule created';
		// 		$payment_schedule->status_date = $payment->date;
		// 		$payment_schedule->save();
		// 		$out[ 'ok' ][] = $payment_schedule->id_payment_schedule;
		// 	}

		// 	$nope = true;
		// }
		// echo json_encode( $out );exit;
		// // script to fix #4020
		// return;
		// return;

		$out = [ 'ok' => [], 'nope' => [] ];

		// Get the payments schedule with error
		$payments_schedule = Cockpit_Payment_Schedule::q( 'SELECT * FROM payment_schedule WHERE status = "' . Cockpit_Payment_Schedule::STATUS_PROCESSING . '" ORDER BY id_payment_schedule ASC' );
		foreach( $payments_schedule as $payment_schedule ){
			$nope = false;

			// Get the payment of the schedule
			$payment = Crunchbutton_Payment::q( 'SELECT * FROM payment WHERE id_driver = "' . $payment_schedule->id_driver . '" AND ROUND( amount, 2 ) = ' . $payment_schedule->amount . ' ORDER BY id_payment DESC' );
			if( $payment->id_payment ){

				// Check if the payment is already related with another payment schedule
				$chedule = Cockpit_Payment_Schedule::q( 'SELECT * FROM payment_schedule WHERE id_payment = ' . $payment->id_payment );
				if( $chedule->count() == 0 ){
					$payment_schedule->id_payment = $payment->id_payment;
					$payment_schedule->status = Cockpit_Payment_Schedule::STATUS_DONE;
					$payment_schedule->log = 'Payment finished';
					$payment_schedule->status_date = $payment->date;

					// Hard codded values !!!!
					$payment_schedule->id_admin = 3;
					$payment_schedule->pay_type = Cockpit_Payment_Schedule::PAY_TYPE_REIMBURSEMENT;

					$payment_schedule->save();
					$out[ 'ok' ][] = [ 'id_payment_schedule' => $payment_schedule->id_payment_schedule, 'id_payment' => $payment->id_payment ];

					// Update payment info if needed
					if( !$payment->id_admin ){
						$payment->id_admin = $payment_schedule->id_admin;
					}

					if( !$payment->pay_type ){
						$payment->pay_type = $payment_schedule->pay_type;
					}

					$payment->save();

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