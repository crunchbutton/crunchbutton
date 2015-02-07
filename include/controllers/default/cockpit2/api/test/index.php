<?php

class Controller_api_test extends Cana_Controller {
	public function init(){
		$restaurant = Crunchbutton_Restaurant::o(107);
		echo '<pre>';var_dump(  $restaurant->smartETA()  );exit();;
		// $order = Order::o( 58027 );
		// Crunchbutton_Order_Eta::create( $order );
	}
}