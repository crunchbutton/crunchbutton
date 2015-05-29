<?php



class Controller_api_test_timeout {

	public function init() {
		$order = Order::o(92768);
		c::timeout(function() use ($order) {
			Crunchbutton_Message_Sms::send([
				'to' => '_PHONE_',
				'message' => 'WITH TIMEOUT - '.$order->name
			]);
		});
		/*
		Crunchbutton_Message_Sms::send([
			'to' => '_PHONE_',
			'message' => 'WITHOUT TIMEOUT'
		]);
		*/
	}	
}

