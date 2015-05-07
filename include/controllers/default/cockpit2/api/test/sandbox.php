<?php

class Controller_Api_Test_Sandbox extends Crunchbutton_Controller_Account {

	public function init() {

		$restaurant = Restaurant::o( 789 );
		echo '<pre>';var_dump( $restaurant->smartETA( true ) );exit();


	}
}