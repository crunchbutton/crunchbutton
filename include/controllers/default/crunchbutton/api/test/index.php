<?php

class Controller_api_test extends Crunchbutton_Controller_Rest {

	public function init(){
		$restaurant = Restaurant::o( 107 );
		// $restaurant->force_calc_hours = true;
		echo $restaurant->json();exit;
		// echo json_encode($restaurant->timeInfo( null, 'cb' ));exit;
		// Crunchbutton_Restaurant_Time::register( 107 );
		// echo '<pre>';var_dump(  );exit();
		// echo json_encode( Crunchbutton_Restaurant_Time::getTime( 107 ) );exit;
		// Crunchbutton_Restaurant_Time::store();
	}
}
