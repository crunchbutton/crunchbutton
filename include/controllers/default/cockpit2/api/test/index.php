<?php

class Controller_api_test extends Cana_Controller {
	public function init(){

		// $order = Order::o( 81986 );
		// $set = new Settlement;
		// echo '<pre>';var_dump( $set->orderExtractVariables( $order ) );exit();

		Cockpit_Community_Closed_Log::save_log();

		die('hard');

		// $community = Crunchbutton_Community::o( 88 );
		// $community->shutDownCommunity( '2015-02-28 16:38:05' );

		// die('hard');

		// echo '<pre>';
		// $communities = Community::q( 'SELECT * FROM community WHERE active = 1' );
		// echo "Driver;Driver TZ;Community;Community TZ";
		// echo "<br>";
		// foreach( $communities as $community ){
		// 	$tz = $community->timezone;
		// 	$drivers = $community->getDriversOfCommunity();
		// 	foreach( $drivers as $driver ){
		// 		if( $driver->timezone != $tz ){
		// 			echo $driver->name;
		// 			echo ";";
		// 			echo $driver->timezone;
		// 			echo ";";
		// 			echo $community->name;
		// 			echo ";";
		// 			echo $community->timezone;
		// 			echo "<br>";
		// 			// echo '<pre>';var_dump( $tz );exit();
		// 		}
		// 	}
		// }



		// echo ini_get('max_input_vars');
		// exit;
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