<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init(){

		$order = Order::o( 243192 );
		$order->textCustomer( Cockpit_Order::I_AM_5_MINUTES_AWAY );


	}
}
