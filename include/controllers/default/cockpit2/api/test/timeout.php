<?php



class Controller_api_test_timeout {

	public function init() {
		c::timeout(function() {
			Crunchbutton_Message_Sms::send([
				'to' => '_PHONE_',
				'message' => 'WITH TIMEOUT'
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

