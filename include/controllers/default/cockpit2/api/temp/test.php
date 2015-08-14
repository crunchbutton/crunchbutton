<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {
	public function init() {

		Crunchbutton_Admin_Shift_Assign_Confirmation::warningDriversBeforeTheirShift();
		echo '<pre>';var_dump( 1 );exit();

		// $community = Community::o( 67 );
		// echo '<pre>';var_dump( $community->assignedShiftHours() );exit();
		// echo '<pre>';var_dump( $community->deliveryHours() );exit();;

			// $r = Restaurant::o( 3756 );
			// echo '<pre>';var_dump( $r->delivery_service );exit();
			// echo $r->closed_message();
			// exit;
	}
}