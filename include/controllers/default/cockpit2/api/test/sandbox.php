<?php

class Controller_Api_Test_Sandbox extends Crunchbutton_Controller_Account {
	public function init() {

		$env = c::getEnv();
		$num = '***REMOVED***';
		$url = 'http://staging.crunchr.co/api/order/138790/sayorderadmin';

		$twilio = new Services_Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
		$call = $twilio->account->calls->create(
			c::config()->twilio->{$env}->outgoingDriver,
			'+1'.$num,
			$url
		);

	}
}