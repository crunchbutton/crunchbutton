<?php

class Crunchbutton_Pexcard_Card extends Crunchbutton_Pexcard_Resource {

	const CARD_STATUS_OPEN = 'OPEN';
	const CARD_STATUS_BLOCKED = 'BLOCKED';
	const LIMTS_EXCEEDED = 'Usage limits exceeded';

	public function card_list(){
		switch ( Crunchbutton_Pexcard_Resource::api_version() ) {
			case 'v4':
				$_cards = ( object ) [ 'body' => [] ];
				$_cards->body = [];
				$cards = Crunchbutton_Pexcard_Details::account();
				if( $cards && $cards->body && $cards->body->Message && $cards->body->Message &&  $cards->body->Message == Crunchbutton_Pexcard_Card::LIMTS_EXCEEDED ){
					return $cards->body->Message;
				}
				if( $cards->body && $cards->body->CHAccountList ){
					foreach( $cards->body->CHAccountList as $card ){
						$_cards->body[] = ( object ) [ 	'id' => $card->AccountId,
																						'firstName' => $card->FirstName,
																						'lastName' => $card->LastName,
																						'ledgerBalance' => $card->LedgerBalance,
																						'availableBalance' => $card->AvailableBalance,
																						'status' => $card->AccountStatus,
																						'cards' => false ];
					}
				}
				return $_cards;
				break;

			default:
				return Crunchbutton_Pexcard_Resource::request( 'cardlist', [] );
				break;
		}
	}

	public function details( $id ){

		switch ( Crunchbutton_Pexcard_Resource::api_version() ) {
			case 'v4':
				$card = Crunchbutton_Pexcard_Details::account( $id );
				$card = $card->body;
				$name = explode( ' ' , $card->ProfileAddress->ContactName );
				$details = ( object ) [ 'body' => [] ];
				$details->body = ( object ) [	'id' => $card->AccountId,
																		 	'firstName' => $name[ 0 ],
																		 	'lastName' => $name[ 1 ],
																		 	'ledgerBalance' => $card->LedgerBalance,
																		 	'availableBalance' => $card->AvailableBalance,
																		 	'status' => $card->AccountStatus,
																		 	'businessId' => $card->AccountStatus,
																		 	'cards' => []
																		 ];
				if( $card->CardList ){
					foreach( $card->CardList as $card ){
						$details->body->cards[] = ( object ) [ 'id' => $card->CardId, 'cardNumber' => $card->Last4CardNumber, 'status' => $card->CardStatus  ];
					}
				}
				return $details;
				break;
			default:
				return Crunchbutton_Pexcard_Resource::request( 'carddetails', [ 'id' => $id ] );
				break;
		}
	}

	public function isOpen( $id ){
		$response = Crunchbutton_Pexcard_Card::details( $id );
		if( $response->body && $response->body->status == Crunchbutton_Pexcard_Card::CARD_STATUS_OPEN ){
			return true;
		}
		return false;
	}

	public function activate_card( $id ){
		Crunchbutton_Pexcard_Resource::request( 'activatecard', [ 'id' => $id ] );
		// Check if the card is open
		if( Crunchbutton_Pexcard_Card::isOpen( $id ) ){
			return true;
		}
		return false;
	}

	public function create( $params = [] ){

		$defaults = [ 'firstName' => null,
									'lastName' => null,
									'dateOfBirth' => '09/05/2012',
									'phoneNumber' => '_PHONE_',
									'email' => '_EMAIL',
									'streetLine1' => '1120 Princeton Drive #7',
									'streetLine2' => null,
									'city' => 'Marina Del Rey',
									'state' => 'California',
									'zip' => '90292' ];

		foreach( $defaults as $key => $val ){
			if( !$params[ $key ] ){
				$params[ $key ] = $val;
			}
		}
		return Crunchbutton_Pexcard_Resource::request( 'createcard', $params );
	}

	public function fund( $id, $amount ){
		return Crunchbutton_Pexcard_Resource::request( 'fund', [ 'id' => $id, 'amount' => $amount ] );
	}

	public function zero( $id ){
		return Crunchbutton_Pexcard_Resource::request( 'zero', [ 'id' => $id ] );
	}

	public function change_status( $id, $status ){
		switch ( Crunchbutton_Pexcard_Resource::api_version() ) {
			case 'v4':
					Crunchbutton_Pexcard_Card::activate_card( $id );
					return Crunchbutton_Pexcard_Resource::request( 'changecardstatus', [ 'id' => $id, 'status' => $status ] );
				break;

			default:
				return Crunchbutton_Pexcard_Resource::request( 'changecardstatus', [ 'id' => $id, 'Status' => $status ] );
				break;
		}
	}

	public function card_block( $id ){
		Crunchbutton_Pexcard_Card::change_status( $id, Crunchbutton_Pexcard_Card::CARD_STATUS_BLOCKED );
	}

	public function card_open( $id ){
		Crunchbutton_Pexcard_Card::change_status( $id, Crunchbutton_Pexcard_Card::CARD_STATUS_OPEN );
	}
}

?>
