<?php

class Crunchbutton_Pexcard_Card extends Crunchbutton_Pexcard_Resource {

	const CARD_STATUS_OPEN = 'OPEN';
	const CARD_STATUS_BLOCKED = 'BLOCKED';

	public function card_list(){
		return Crunchbutton_Pexcard_Resource::request( 'cardlist', [] );
	}

	public function details( $id ){
		return Crunchbutton_Pexcard_Resource::request( 'carddetails', [ 'id' => $id ] );
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
		// for tests
		if( intval( $id ) == 100254 || ( intval( $id ) == 100296 ) ){
			return Crunchbutton_Pexcard_Resource::request( 'fund', [ 'id' => $id, 'amount' => $amount ] );
		}
	}

	public function change_status( $id, $status ){
		return Crunchbutton_Pexcard_Resource::request( 'changecardstatus', [ 'id' => $id, 'status' => $status ] );
	}

	public function card_block( $id ){
		Crunchbutton_Pexcard_Card::change_status( $id, Crunchbutton_Pexcard_Card::CARD_STATUS_BLOCKED );
	}

	public function card_open( $id ){
		Crunchbutton_Pexcard_Card::change_status( $id, Crunchbutton_Pexcard_Card::CARD_STATUS_OPEN );
	}
}

?>
