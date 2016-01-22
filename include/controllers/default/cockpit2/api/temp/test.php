<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init(){

		$q = Crunchbutton_Queue_Notification_Minutes_Way::o( 2162915 );
		$q->run();
		die('hard');

		$order = Order::o( 243192 );
		$order->textCustomer( Cockpit_Order::I_AM_5_MINUTES_AWAY );


	}
}
