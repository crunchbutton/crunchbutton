<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init(){
		$order = Order::o( 220603 );
		echo $order->message( 'sms-admin' );
	}
}