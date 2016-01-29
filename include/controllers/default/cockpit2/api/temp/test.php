<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init(){
			Crunchbutton_Message_Sms::send([
			'to' => '***REMOVED***',
			'message' => 'testing',
			'reason' => Crunchbutton_Message_Sms::REASON_CUSTOMER_ORDER
		]);

	}
}
