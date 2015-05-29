<?php

class Crunchbutton_Stripe_Credit extends Cana_Model {

	public static function credit( $stripe_account_id, $amount, $note = 'Payout' ) {

		if( !$stripe_account_id ){
			return false;
		}

		try {

			$env = c::getEnv();

			\Stripe\Stripe::setApiKey( c::config()->stripe->{ $env }->secret );

			$amount = $amount * 100;

			$transfer = \Stripe\Transfer::create(
											array(
												'amount' => $amount,
												'currency' => 'usd',
												'destination' => $stripe_account_id,
												'statement_descriptor' => $note
											) );

			if( $transfer->id ){
				return $transfer;
			}


		} catch (Exception $e) {
			throw new Exception( $e->description );
			print_r($e);
			exit;
		}
		return false;
	}
}