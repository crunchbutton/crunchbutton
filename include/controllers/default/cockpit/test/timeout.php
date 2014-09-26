<?php



class Controller_test_timeout extends Crunchbutton_Controller_RestAccount {

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

