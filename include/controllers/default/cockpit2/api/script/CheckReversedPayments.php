<?php

class Controller_Api_Script_CheckReversedPayments extends Crunchbutton_Controller_RestAccount {

	public function init() {
		header( 'Content-Type: text/html' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		echo "<pre>";
		$payments = Payment::q( 'SELECT payment.*, admin.name FROM payment
															INNER JOIN admin ON admin.id_admin = payment.id_driver
															WHERE DATE_FORMAT( payment.date, "%Y%m%d" ) = "20151022" AND payment.id_driver IS NOT NULL AND payment.stripe_id IS NOT NULL' );
		foreach( $payments as $payment ){
			if( !$payment->wasReversed() ){
				echo "id_payment: {$payment->id_payment}	stripe:<a target='_blank' href='https://dashboard.stripe.com/transfers/{$payment->stripe_id}'>{$payment->stripe_id}</a>	amount:{$payment->amount}	driver:{$payment->name}\n";
			} else {
				echo "REVERSED: id_payment: {$payment->id_payment}	stripe:<a target='_blank' href='https://dashboard.stripe.com/transfers/{$payment->stripe_id}'>{$payment->stripe_id}</a>	amount:{$payment->amount}	driver:{$payment->name}\n";
			}
		}
	}
}