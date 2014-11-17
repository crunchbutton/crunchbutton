<?php

class TwilioTest extends PHPUnit_Framework_TestCase {
	public function testSms() {

		$res = Crunchbutton_Message_Sms::send([
			'to' => '_PHONE_',
			'message' => 'TWILIO-TRAVIS-TEST'
		]);

		$this->assertTrue($res[0]->sid ? true : false);
	}
}