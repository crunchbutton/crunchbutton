<?php

class Controller_api_test extends Cana_Controller {
	public function init(){

		$num = '***REMOVED***';

		Crunchbutton_Message_Sms::send([
			'to' => $num,
			'message' => 'test',
			'reason' => Crunchbutton_Message_Sms::REASON_CUSTOMER_ORDER
		]);

	}
}