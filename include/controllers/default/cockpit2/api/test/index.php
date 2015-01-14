<?php

class Controller_api_test extends Cana_Controller {
	public function init(){

		$num = '2037728167';

		Crunchbutton_Message_Sms::send([
			'to' => $num,
			'message' => "YAY! Crunchbutton for iPhone!\nhttp://_DOMAIN_/app",
			'reason' => Crunchbutton_Message_Sms::REASON_APP_DOWNLOAD
		]);

	}
}