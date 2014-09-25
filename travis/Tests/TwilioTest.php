<?php

class TwilioTest extends PHPUnit_Framework_TestCase {
	public function testSms() {
	/*
		$env = c::getEnv();
		$twilio = c::twilio();

		$res = $twilio->account->sms_messages->create(
			c::config()->twilio->{$env}->outgoingTextDriver,
			'+1_PHONE_',
			'TWILIO-TRAVIS-TEST'
		);
		*/
		
		$res = Crunchbutton_Message_Sms::send([
			'to' => '_PHONE_',
			'message' => 'TWILIO-TRAVIS-TEST'
		]);

		$this->assertTrue($res[0]->sid ? true : false);
	}
}