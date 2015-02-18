<?php

// payment paying drivers for all shifts ever worked? #4799

class Controller_Api_Script_RevertPayments extends Crunchbutton_Controller_RestAccount {

	public function init() {
		Crunchbutton_Settlement::revertPaymentByPaymentId( 11960 );
		Crunchbutton_Settlement::revertPaymentByPaymentId( 11969 );
		Crunchbutton_Settlement::revertPaymentByPaymentId( 11978 );
		Crunchbutton_Settlement::revertPaymentByPaymentId( 11984 );
		Crunchbutton_Settlement::revertPaymentByPaymentId( 11990 );
		Crunchbutton_Settlement::revertPaymentByPaymentId( 11993 );
		Crunchbutton_Settlement::revertPaymentByPaymentId( 12023 );
		Crunchbutton_Settlement::revertPaymentByPaymentId( 12041 );
		Crunchbutton_Settlement::revertPaymentByPaymentId( 12047 );
		Crunchbutton_Settlement::revertPaymentByPaymentId( 12062 );
		Crunchbutton_Settlement::revertPaymentByPaymentId( 11948 );
	}
}