<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init(){
		// $restaurant = Restaurant::o( 107 );
		// echo json_encode($restaurant->timeInfo( null, 'cockpit' ));exit;
		Crunchbutton_Restaurant_Time::store( 'America/New_York' );

		die('hard');
		if( $_GET[ 'id_restaurant' ] ){
			echo json_encode( Crunchbutton_Restaurant_Time::getTime( $_GET[ 'id_restaurant' ] ) );exit;
		}
	}
}