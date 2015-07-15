<?php

class Controller_Api_Script_DriversPaymentsFix extends Crunchbutton_Controller_RestAccount {

	public function init() {

		$out = [ 'ok' => [], 'nope' => [] ];

		$type = Cockpit_Payment_Schedule::PAY_TYPE_REIMBURSEMENT;
		$type = Cockpit_Payment_Schedule::PAY_TYPE_PAYMENT;
		// AND id_payment_schedule = 2019
		$schedules = Cockpit_Payment_Schedule::q('SELECT * FROM payment_schedule WHERE amount IS NULL AND pay_type = ? AND id_admin IS NULL AND status = ? ORDER BY id_payment_schedule ASC', [$type, Cockpit_Payment_Schedule::STATUS_DONE]);
		// $schedules = Cockpit_Payment_Schedule::q( 'SELECT * FROM payment_schedule WHERE id_payment_schedule IN( 2096 )' );
		echo 'ID Admin,ID Payment schedule,Name,Payment Date,Period,Orders,Tip,CB Fee,Amount,URL';
		foreach( $schedules as $schedule ){
			$calc = $this->_checkPaymentIsOK( $schedule, $type );
			if( $calc[ 'amount' ] > 0 ){
				echo "\n";
				echo $schedule->id_driver; echo ',';
				echo $schedule->id_payment_schedule; echo ',';
				echo $schedule->driver()->name; echo ',';
				echo $schedule->date()->format( 'm/d/Y' ); echo ',';
				echo $schedule->range_date; echo ',';
				echo $calc[ 'orders' ]; echo ',';
				echo $calc[ 'tip' ]; echo ',';
				echo $calc[ 'markup' ]; echo ',';
				echo $calc[ 'amount' ]; echo ',';
				echo "https://cockpit.la/drivers/payment/" . $schedule->id_payment_schedule;

			} else {
				$out[ 'ok' ][ $schedule->id_payment_schedule ] = true;
			}
		}
		// echo json_encode( $out );exit();

	}
// should recalculate shifts and check if the payment is hourly


	public function _checkPaymentIsOK( $schedule, $type ){

		$payment = 0;
		$orders = 0;
		$tip = 0;
		$summary = Settlement::driverSummary( $schedule->id_payment_schedule );
// echo json_encode( $summary );exit;
		if( $summary[ 'driver_payment_hours' ] == 1 ){
			$range_date = $summary[ 'range_date' ];
			$range_date = explode( ' => ', $range_date );
			$shifts = Settlement::workedShiftsByPeriod( $summary[ 'id_driver' ], [ 'start' => $range_date[ 0 ], 'end' => $range_date[ 1 ] ] );
			// echo '<pre>';var_dump( $shifts );exit();
		}

		//  GET THE ORDERS RANGE IN ORDER TO CALCULATE THE CORRECT RANGE

		// echo json_encode( $summary );exit;
		foreach( $summary[ 'orders' ][ 'included' ] as $order ){


			if( $type == Cockpit_Payment_Schedule::PAY_TYPE_REIMBURSEMENT ){
				$schedule_order = Payment_Schedule_Order::q( 'SELECT * FROM payment_schedule_order WHERE id_order = ' . $order[ 'id_order' ] . ' AND FORMAT( amount, 2 ) = ' . $order[ 'total_reimburse' ] );
				if( !$schedule_order->id_payment_schedule ){
					$payment += $order[ 'total_reimburse' ];
				}
			} else {
				$schedule_order = Payment_Schedule_Order::q( 'SELECT * FROM payment_schedule_order WHERE id_order = ' . $order[ 'id_order' ] . ' AND FORMAT( amount, 2 ) = ' . $order[ 'total_payment' ] );
				if( !$schedule_order->id_payment_schedule ){
					$payment += $order[ 'total_payment' ];
				}
			}
			$tip += $order[ 'tip' ];
			$orders++;
		}
		return [ 'amount' => $payment, 'orders' => $orders, 'tip' => $tip, 'markup' => $summary[ 'calcs' ][ 'markup' ] ];
	}

}