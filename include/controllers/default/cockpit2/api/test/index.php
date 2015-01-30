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


		// $pex = new Crunchbutton_Pexcard_Resource;
		// $this->e( $pex->ping() );

		// $business = new Crunchbutton_Pexcard_Business;
		// $this->e( $business->profile() );

		// $business = new Crunchbutton_Pexcard_Business;
		// $this->e( $business->admin() );
		// $this->e( $business->admin( 1051 ) );

		$details = new Crunchbutton_Pexcard_Details;
		$this->e( $details->account() );


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