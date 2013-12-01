<?php

class Controller_api_download extends Crunchbutton_Controller_Rest {
	public function init() {
		$input = $_REQUEST['num'];

		if (!$input) {
			$input == c::user()->phone;
		}

		// trim whitespace
		$num = trim($input);
		
		// get rid of non numbers
		$num = preg_replace('/[^\d]/','',$input);
		
		// trincate
		$num = substr($num, 0, 10);

		// remove 0 and 1 starters
		$num = preg_replace('/^0|^1/','',$num);

		if ($num != $input || !$num) {
			echo json_encode(['error' => 'invalid phone number', 'status' => false]);
			exit;
		}

		$env = c::getEnv();		
		$twilio = new Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
		
		$msg = 'YAY! Crunchbutton for iPhone! '."\n".'http://_DOMAIN_/app';
		
		$twilio->account->sms_messages->create(
			c::config()->twilio->{$env}->outgoingTextCustomer,
			'+1'.$num,
			$msg
		);

		echo json_encode(['status' => true]);

		exit;
	}
}