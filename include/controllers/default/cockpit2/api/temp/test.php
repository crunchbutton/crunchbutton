<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {
	public function init() {
		// $assignment = Crunchbutton_Admin_Shift_Assign::o( 62233 );
		// Crunchbutton_Admin_Shift_Assign_Confirmation::confirm( $assignment );
		// Crunchbutton_Admin_Shift_Assign_Confirmation::warnCS( $assignment );
		// $community = Community::o( 194 );
		// $community->getDriversOfCommunity();
		// $community->shutDownCommunity();
		// Crunchbutton_Community::shutDownCommunities();
		// echo 'done';

		$check_name = 'Crunchbutton';
		$check_address = '1120 Princeton Dr. #7';
		$check_address_city = 'Marina Del Rey';
		$check_address_state = 'CA';
		$check_address_zip = '90292';
		$check_address_country = $payment_type->check_address_country;

		$contact_name = 'Judd Stern Rosenblatt';

		$error = false;

		$amount = 1;

		try{
			$c = c::lob()->checks()->create( [ 'name' => $check_name,
																					'to' => [ 'name' => $contact_name,
																										'address_line1' => $check_address,
																										'address_city' => $check_address_city,
																										'address_state' => $check_address_state,
																										'address_zip' => $check_address_zip,
																										'address_country' => $check_address_country ],
																					'bank_account' => c::lob()->defaultAccount(),
																					'amount' => $amount,
																					'memo' => 'Check test',
																					'message' => 'Check test' ] );
			$check_id = $c->id;
		} catch( Exception $e ) {
			echo '<pre>';var_dump( $e->getMessage() );exit();
		}
		echo '<pre>';var_dump( $c );exit();

	}
}