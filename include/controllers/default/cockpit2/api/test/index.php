<?php

class Controller_api_test extends Cana_Controller {

	public function e( $request ){
		if( $request->body ){
			echo json_encode( $request->body );exit;
		} else {
			echo '<pre>';var_dump( $request );exit();
		}
	}

	public function init(){


		// $token = Crunchbutton_Pexcard_Token::getToken();
		// echo '<pre>';var_dump( $token );exit();

		// Crunchbutton_Pexcard_Transaction::loadTransactions();
		// die('hard');

		// $transactions = Crunchbutton_Pexcard_Transaction::transactions( '01/01/2015', '01/30/2015' );
		// echo json_encode( $transactions );exit;

		// Crunchbutton_Pexcard_Monitor::checkBalanceLimit();

		// $pexcard = Cockpit_Admin_Pexcard::getByPexcard( 6203 );
		// echo '<pre>';var_dump( $pexcard->addArbitraryFunds( -110, 'just testing 1' ) );exit();;

		// $funds = Crunchbutton_Pexcard_Card::addArbitraryFunds( 6203, 10.00 );
		// echo '<pre>';var_dump( $funds );exit();
		// $this->e( $funds );

		// $pexcard = Cockpit_Admin_Pexcard::getByPexcard( 6203 );
		// echo '<pre>';var_dump( $pexcard->addArbitraryFunds( 10.00, 'testing' ) );exit();;


		// $pex = new Crunchbutton_Pexcard_Resource;
		// $this->e( $pex->ping() );

		$business = new Crunchbutton_Pexcard_Business;
		$this->e( $business->profile() );

		// $business = new Crunchbutton_Pexcard_Business;
		// $this->e( $business->admin() );
		// $this->e( $business->admin( 1051 ) );

		// $details = new Crunchbutton_Pexcard_Details;
		// $this->e( $details->account( 6202 ) );
		// $this->e( $details->account() );


		// $card = Crunchbutton_Pexcard_Details::cards( 6202 );
		// $this->e( $card );



		$this->e( Crunchbutton_Pexcard_Card::details( 6203 ) );

		// $status = Crunchbutton_Pexcard_Card::change_status( 6282, Crunchbutton_Pexcard_Card::CARD_STATUS_OPEN );
		// $status = Crunchbutton_Pexcard_Card::activate_card( 6203 );
		// $this->e( $status );



		$cards = Crunchbutton_Pexcard_Card::card_list();
		foreach( $cards->body as $card ){
			$_cards = Crunchbutton_Pexcard_Details::cards( $card->id );
			echo '<pre>';var_dump( $_cards );exit();
		}
echo '<pre>';var_dump( $cards );exit();
		$card = new Crunchbutton_Pexcard_Card;
		$card_create = $card->create( [ 'FirstName' => 'Daniel',
																						'LastName' => 'Camargo',
																						'DateOfBirth' => '09/05/2012',
																						'Phone' => '_PHONE_',
																						'ShippingPhone' => '_PHONE_',
																						'ShippingMethod' => 'Expedited',
																						'Email' => '_EMAIL',

																						'ProfileAddress' => [
																								'ContactName' => 'David',
																								'AddressLine1' =>'1120 Princeton Drive #7',
																								'AddressLine2' => '',
																								'City' => 'Marina Del Rey',
																								'State' =>'California',
																								'PostalCode' => '90292',
																								'Country' => 'USA'
																							],

																						'ShippingAddress' => [
																								'ContactName' => 'David',
																								'AddressLine1' =>'1120 Princeton Drive #7',
																								'AddressLine2' => '',
																								'City' => 'Marina Del Rey',
																								'State' =>'California',
																								'PostalCode' => '90292',
																								'Country' => 'USA'
																							] ] );
		$this->e( $card_create );


		// $start = '01/01/2015';
		// $end = '01/25/2015';
		// $tranactios = Crunchbutton_Pexcard_Transaction::transactions( $start, $end );
		$this->e( $tranactios );

	}

}