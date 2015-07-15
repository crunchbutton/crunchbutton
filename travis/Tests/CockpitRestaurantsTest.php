<?php

/**
 * Testing to see if the endpoints throw any sort of error
 */
/*
class CockpitRestaurantsTest extends PHPUnit_Framework_TestCase {
	public static function setUpBeforeClass() {
		//$res = c::auth()->doAuthByLocalUser(['email' => 'root', 'password' => 'password']);
	}
	public function testRestaurant() {
		// set the url to go to

		c::app()->pages(['api', 'restaurant', '1']);
		
		// start output buffer and run page
		//ob_start();
		Cana::app()->displayPage();
		//$res = ob_get_contents();
		//ob_end_clean();
		//$res = json_decode($res);

		// do something
		print_r($res);
		exit;
		$c = c::db()->get('select * from config limit 1')->get(0);
		$this->assertTrue($c->id_config ? true : false);
	}
	public function testBasic() {
		// set the url to go to
		c::app()->pages(['api', 'restaurants']);
		
		// start output buffer and run page
		ob_start();
		Cana::app()->displayPage();
		$res = ob_get_contents();
		ob_end_clean();
		$res = json_decode($res);

		// do something
		print_r($res);
		exit;
		$c = c::db()->get('select * from config limit 1')->get(0);
		$this->assertTrue($c->id_config ? true : false);
	}
}
*/