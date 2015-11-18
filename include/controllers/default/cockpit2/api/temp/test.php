<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init(){
		// $restaurant = Restaurant::o( 107 );
		// echo json_encode($restaurant->timeInfo( null, 'cockpit' ));exit;
		// Crunchbutton_Restaurant_Time::store( 'America/New_York' );
		// Crunchbutton_Queue::process();
		// 491019

		if( $_GET[ 'id_queue' ] ){
			$q = Crunchbutton_Queue_Restaurant_Time::q( $_GET[ 'id_queue' ] );
			$q->run();
			die('hard');
		}

		if( $_GET[ 'tz' ] ){
			Crunchbutton_Restaurant_Time::store( $_GET[ 'tz' ] );
			die('hard');
		}
	}
}