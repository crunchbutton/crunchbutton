<?php

class Controller_Api_Script_DriversPaymentsFix extends Crunchbutton_Controller_RestAccount {

	public function init() {

		$out = [ 'ok' => [], 'nope' => [] ];

		$type = Cockpit_Payment_Schedule::PAY_TYPE_REIMBURSEMENT;
		$type = Cockpit_Payment_Schedule::PAY_TYPE_PAYMENT;
		// AND id_payment_schedule = 2019
		$schedules = Cockpit_Payment_Schedule::q( 'SELECT * FROM payment_schedule WHERE amount IS NULL AND pay_type = "' . $type . '" AND id_admin IS NULL AND status = "' . Cockpit_Payment_Schedule::STATUS_DONE . '" ORDER BY id_payment_schedule ASC' );
		// $schedules = Cockpit_Payment_Schedule::q( 'SELECT * FROM payment_schedule WHERE id_payment_schedule IN( 1400,1557,1618,1652,1692,1731,1891,1919,1950,2019 )' );
		foreach( $schedules as $schedule ){
			$amount = $this->_checkPaymentIsOK( $schedule, $type );
			if( $amount > 0 ){
				// $out[ 'nope' ][ $schedule->id_payment_schedule ] = [ 'amount' => $amount, 'id_payment' => $schedule->id_payment ];
				echo "https://cockpit.la/drivers/payment/" . $schedule->id_payment_schedule;
				echo "\n";
				echo $schedule->driver()->name;
				echo "\nAmount: ";
				echo $amount;
				echo "\n";
				echo "\n";
			} else {
				$out[ 'ok' ][ $schedule->id_payment_schedule ] = true;
			}
		}
		echo json_encode( $out );exit();

	}



	public function _checkPaymentIsOK( $schedule, $type ){
		$settlement = new Settlement;
		$payment = 0;
		$summary = $settlement->driverSummary( $schedule->id_payment_schedule );
		foreach( $summary[ 'orders' ][ 'included' ] as $order ){
			if( $type == Cockpit_Payment_Schedule::PAY_TYPE_REIMBURSEMENT ){
				$payment += $order[ 'total_reimburse' ];
			} else {
				$payment += $order[ 'total_payment' ];
			}
		}
		return $payment;
	}

}