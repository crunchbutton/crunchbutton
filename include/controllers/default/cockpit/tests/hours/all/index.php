<?php

class Controller_tests_hours_all extends Crunchbutton_Controller_Account {
	public function init() {
		$restaurants = Restaurant::q( 'SELECT * FROM restaurant WHERE active = true ORDER BY name ASC' );
		c::view()->restaurants = $restaurants;
		c::view()->display('tests/hours/all/index');
	}
}