<?php

// payment paying drivers for all shifts ever worked? #4799

class Controller_Api_Script_RevertPayments extends Crunchbutton_Controller_RestAccount {

	public function init() {
		$payments = Payment::q( 'SELECT * FROM payment_schedule WHERE range_date = "02/09/2009 => 02/15/2015" AND pay_type = "payment" AND arbritary != 1 ' );
		$count = 1;
		foreach( $payments as $payment ){
			echo $count . " => ";
			echo $payment->id_payment_schedule;
			echo "\n";
			$count++;
			Crunchbutton_Settlement::revertPaymentByScheduleId( $payment->id_payment_schedule );
		}
	}
}