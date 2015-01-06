<?php

class Crunchbutton_Pexcard_Monitor extends Crunchbutton_Pexcard_Resource {

	const BALANCE_LIMIT = 500;
	const TRANSFER_LIMIT = 300;

	public function checkBalanceLimit(){
		$cards = Crunchbutton_Pexcard_Card::card_list();
		$pattern = "The card serial %d, last four %d has US$%s. It belongs to %s.";
		foreach( $cards->body as $card ){
			if( $card->ledgerBalance >= Crunchbutton_Pexcard_Monitor::BALANCE_LIMIT ||
					$card->availableBalance >= Crunchbutton_Pexcard_Monitor::BALANCE_LIMIT ){
				$pexcard = Cockpit_Admin_Pexcard::getByPexcard( $card->id );
				$message = sprintf( $pattern, $pexcard->card_serial, $pexcard->last_four, strval( $card->ledgerBalance ), $pexcard->admin()->name );
				Crunchbutton_Support::createNewWarning( [ 'body' => $message ] );
			}
		}
	}

	public function balancedExcededLimit( &$card, $amount, $note ){
		$pexcard = Cockpit_Admin_Pexcard::getByPexcard( $card->id );
		$pattern = "The card serial %d, last four %d did not receive US$%s. It has US$ %s and belongs to %s. Note: %s.";
		$message = sprintf( $pattern, $pexcard->card_serial, $pexcard->last_four, strval( $amount ), strval( $card->ledgerBalance ), $pexcard->admin()->name, $note );
		Crunchbutton_Support::createNewWarning( [ 'body' => $message ] );
		return $message;
	}

}

?>
