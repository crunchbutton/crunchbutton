<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init(){
		// $restaurant = Restaurant::o( 107 );
		// echo json_encode($restaurant->timeInfo( null, 'cockpit' ));exit;
		echo json_encode( Crunchbutton_Restaurant_Time::getTime( 107 ) );exit;
	}
}