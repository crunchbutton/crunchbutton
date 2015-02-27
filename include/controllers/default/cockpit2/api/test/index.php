<?php

class Controller_api_test extends Cana_Controller {
	public function init(){
		echo ini_get('max_input_vars');
		exit;
		// echo '<pre>';var_dump( $_REQUEST[ 'cockpit' ], $_SERVER['HTTP_HOST'] );exit();
		// test
		// $agent = Crunchbutton_Agent::getAgent();


		// $dt = '2015-02-20 08:02:03';

		// $restaurant = Restaurant::o( 828 );
		// echo '<pre>';var_dump( $restaurant->open( $dt ) );exit();

		// $community = Community::o( 70 );

		// echo '<pre>';var_dump( $community->forceCloseLog() );exit();;
		// $community->reopenAutoClosedCommunity( $dt );
		// Community::shutDownCommunities();

	}
}