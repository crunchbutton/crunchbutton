<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {
	public function init() {

		Crunchbutton_Community::smartSortPopulation();



		// echo '<pre>';var_dump( $q->run() );exit();;
// echo '<pre>';var_dump( $q );exit();

		// $r = Crunchbutton_Message_Push_Ios::send([
		// 	'to' => 'b85dc7710abcd6a18aa6ff91ca165aa97fa02df23323d49c689a7d50fd47e800',
		// 	'message' => 'test',
		// 	'count' => 1,
		// 	'id' => 'order-1',
		// 	'category' => 'order-new-test',
		// 	'env' => c::getEnv()
		// ]);

		// var_dump($r);

		// Crunchbutton_Admin_Shift_Assign_Confirmation::warningDriversBeforeTheirShift();
		// echo '<pre>';var_dump( 1 );exit();

		// $community = Community::o( 67 );
		// echo '<pre>';var_dump( $community->assignedShiftHours() );exit();
		// echo '<pre>';var_dump( $community->deliveryHours() );exit();

			// $r = Restaurant::o( 3756 );
			// echo '<pre>';var_dump( $r->delivery_service );exit();
			// echo $r->closed_message();
			// exit;
			//Crunchbutton_Community::shutDownCommunities();
	}
}