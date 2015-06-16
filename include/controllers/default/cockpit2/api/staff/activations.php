<?php

class Controller_api_staff_activations extends Crunchbutton_Controller_RestAccount {

	public function init() {
		$invites = Crunchbutton_Referral::q( 'SELECT r.*, o.name AS customer, o.date AS date, psr.id_payment_schedule_referral FROM referral r
																						INNER JOIN `order` o ON r.id_order = o.id_order
																						LEFT JOIN payment_schedule_referral psr ON r.id_referral = psr.id_referral
																						WHERE r.id_admin_inviter = ? ORDER BY r.id_referral DESC', [ c::user()->id_admin ] );
		$out = [ 'paid' => 0, 'not_paid' => 0, 'total' => 0, 'not_paid_activations' => [] ];
		foreach( $invites as $invite ){
			if( $invite->id_payment_schedule_referral ){
				$out[ 'paid' ]++;
			} else {
				$out[ 'not_paid' ]++;
				$out[ 'not_paid_activations' ][] = [ 'customer' => $invite->customer, 'date' => $invite->date()->format( 'M jS Y' ) ];
			}
			$out[ 'total' ]++;
		}
		echo json_encode( $out );exit;

	}
}