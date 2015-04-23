<?php

class Controller_Api_Test_Sandbox extends Crunchbutton_Controller_Account {

	public function init() {

		$restaurant = Restaurant::o( 107 );
		echo '<pre>';var_dump( $restaurant->isImageUsedByOtherRestaurant() );exit();

		// Crunchbutton_Pexcard_Transaction::convertTimeZone();
		// Crunchbutton_Pexcard_Transaction::loadTransactions();

	}
}