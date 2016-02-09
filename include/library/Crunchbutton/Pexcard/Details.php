<?php

class Crunchbutton_Pexcard_Details extends Crunchbutton_Pexcard_Resource {

	public function account( $AccountId = null ){
		// Get cached results
		$content = Crunchbutton_Pexcard_Resource::cache( $AccountId );
		if( !$content ){
			$content = Crunchbutton_Pexcard_Resource::request( 'detailsaccount', [ 'id' => $AccountId ] );
			$content = Crunchbutton_Pexcard_Resource::saveCache( $content, $AccountId );
		}
		return $content;
	}

	public function cards( $AccountId = null ){
		$cards = Crunchbutton_Pexcard_Details::account( $AccountId );
		$_cards = ( object ) [ 'body' => [] ];
		$_cards = [];
		if( $cards->body && $cards->body->CHAccountList ){
			foreach( $cards->body->CHAccountList as $card ){
				$_cards[] = ( object ) [ 'id' => $card->AccountId, 'status' => $card->AccountStatus, 'LedgerBalance' => $card->LedgerBalance, 'AvailableBalance' => $card->AvailableBalance, 'cardNumber' => $card->LastName ];
			}
			return $_cards;
		}
	}
}

?>
