<?php



class Controller_api_test_timeout {

	public function init() {
		$order = Order::o(84);
		//$order = null;
		
		$func = function() use ($order) {
			Crunchbutton_Message_Sms::send([
				'to' => '_PHONE_',
				'message' => 'WITH TIMEOUT - '.$order->name
			]);
		};
		
		$closure = new SuperClosure($func);
		$encoded = base64_encode(serialize($closure));

		//$encoded = str_replace("'",'"',escapeshellarg($encoded));

		//$c = unserialize();

		$c = unserialize(base64_decode($encoded));
		$c->__invoke();

		

		//c::timeout($func);
		
		
		/*
		Crunchbutton_Message_Sms::send([
			'to' => '_PHONE_',
			'message' => 'WITHOUT TIMEOUT'
		]);
		*/
	}	
}

