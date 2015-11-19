<?php

class VendorTest extends PHPUnit_Framework_TestCase {
	public function testCana() {
		new Cana;
		$this->assertTrue(true);
	}

	public function testTwilio() {
		new Services_Twilio('test','test');
		$this->assertTrue(true);
	}

	public function testStripe() {
		\Stripe\Stripe::setApiKey('test');
		$this->assertTrue(true);
	}

	public function testScss() {
		$scss = new \Leafo\ScssPhp\Compiler;
		$this->assertTrue(true);
	}

	public function testHttpful() {
		\Httpful\Request::get('file://');
		$this->assertTrue(true);
	}

	public function testMailgun() {
		new \Mailgun\Mailgun('');
		$this->assertTrue(true);
	}

	public function testPredis() {
		new Predis\Client;
		$this->assertTrue(true);
	}

	public function testApnsPHP() {
		if (class_exists('ApnsPHP_Push')) {
			$this->assertTrue(true);
		} else {
			$this->assertTrue(false);
		}
	}

	public function testGithub() {
		new \Github\Client();
		$this->assertTrue(true);
	}


}

