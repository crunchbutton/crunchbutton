<?php

class Controller_Api_Script_RevertPayments extends Crunchbutton_Controller_RestAccount {

	public function init() {

		// payment paying drivers for all shifts ever worked? #4799
		// $payments = Payment::q( 'SELECT * FROM payment_schedule WHERE range_date = "02/09/2009 => 02/15/2015" AND pay_type = "payment" AND arbritary != 1 ' );
		$payments = Payment_Schedule::q( "SELECT ps.* FROM payment_schedule ps WHERE DATE( ps.date ) = '2015-02-17' AND ps.pay_type = 'reimbursement' AND arbritary != 1" );
		$count = 1;
		foreach( $payments as $schedule ){
			$remove = true;
			$payment = $schedule->payment();
			if( $payment->id_payment && $payment->balanced_id ){
				$remove = false;
			}
			if( $remove ){
				echo "REMOVED::: ";
				echo $count . " => ";
				echo $schedule->id_payment_schedule;
				echo "\n";
				$count++;
				Crunchbutton_Settlement::revertPaymentByScheduleId( $schedule->id_payment_schedule );
			} else {
				echo "KEPT::: ";
				echo $count . " => ";
				echo $schedule->id_payment_schedule;
				echo "\n";
				$count++;
			}
		}
	}
}