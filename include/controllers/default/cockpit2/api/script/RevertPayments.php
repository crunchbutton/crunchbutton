<?php

// payment paying drivers for all shifts ever worked? #4799

class Controller_Api_Script_RevertPayments extends Crunchbutton_Controller_RestAccount {

	public function init() {
		die('remove this line');
		$payments = Payment::q( 'SELECT * FROM payment_schedule WHERE range_date = "02/09/2009 => 02/15/2015" AND pay_type = "payment"' );
		foreach( $payments as $payment ){
			Crunchbutton_Settlement::revertPaymentByScheduleId( $payment->id_payment_schedule );
		}
	}
}