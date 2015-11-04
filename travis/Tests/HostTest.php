<?php

class HostTest extends PHPUnit_Framework_TestCase {
	public function testCockpit() {
		$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = 'cockpit.la';

		$this->assertTrue(c::isCockpit());
		$this->assertEquals('live', c::app()->envByHost());
	}

	public function testCockpitOld() {
		$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = 'old.cockpit._DOMAIN_';
		$this->assertTrue(c::isCockpit());
	}

	public function testCockpitBeta() {
		$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = 'beta.cockpit.la';
		$this->assertTrue(c::isCockpit());
	}

	public function testCockpitLocal() {
		$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = 'cockpit3.localhost';
		$this->assertTrue(c::isCockpit());
	}

	public function testCockpitLocalOld() {
		$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = 'cockpit.localhost';
		$this->assertTrue(c::isCockpit());
	}

	public function testCrunchbutton() {
		$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = '_DOMAIN_';

		$this->assertFalse(c::isCockpit());
		$this->assertEquals('live', c::app()->envByHost());
	}

	public function testHomeBeta() {
		$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = 'beta.crunchr.co';
		$this->assertFalse(c::isCockpit($_SERVER['HTTP_HOST']));
	}

	public function testHomeStaging() {
		$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = 'staging.crunchr.co';
		$this->assertFalse(c::isCockpit($_SERVER['HTTP_HOST']));
	}

	public function testHomeLocal() {
		$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = 'crunchbutton.localhost';
		$this->assertFalse(c::isCockpit($_SERVER['HTTP_HOST']));
	}

	public function testCrunchbuttonStaging() {
		$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = 'dev.staging.crunchbutton.crunchr.co';

		$this->assertFalse(c::isCockpit());
		$this->assertEquals('beta', c::app()->envByHost());


		$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = 'live.staging.crunchbutton.crunchr.co';

		$this->assertFalse(c::isCockpit());
		$this->assertEquals('live', c::app()->envByHost());


		$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = 'staging.crunchbutton.crunchr.co';

		$this->assertFalse(c::isCockpit());
		$this->assertEquals('live', c::app()->envByHost());
	}

	public function testCrunchbuttonCi() {
		$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = 'dev.ci.crunchbutton.crunchr.co';

		$this->assertFalse(c::isCockpit());
		$this->assertEquals('beta', c::app()->envByHost());


		$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = 'live.ci.crunchbutton.crunchr.co';

		$this->assertFalse(c::isCockpit());
		$this->assertEquals('live', c::app()->envByHost());


		$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = 'ci.crunchbutton.crunchr.co';

		$this->assertFalse(c::isCockpit());
		$this->assertEquals('live', c::app()->envByHost());
	}

	public function testCockpitStaging() {
		$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = 'dev.staging.cockpit.crunchr.co';

		$this->assertTrue(c::isCockpit());
		$this->assertEquals('beta', c::app()->envByHost());


		$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = 'live.staging.cockpit.crunchr.co';

		$this->assertTrue(c::isCockpit());
		$this->assertEquals('live', c::app()->envByHost());


		$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = 'staging.cockpit.crunchr.co';

		$this->assertTrue(c::isCockpit());
		$this->assertEquals('live', c::app()->envByHost());
	}

	public function testCockpitCi() {
		$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = 'dev.ci.cockpit.crunchr.co';

		$this->assertTrue(c::isCockpit());
		$this->assertEquals('beta', c::app()->envByHost());


		$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = 'live.ci.cockpit.crunchr.co';

		$this->assertTrue(c::isCockpit());
		$this->assertEquals('live', c::app()->envByHost());


		$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = 'ci.cockpit.crunchr.co';

		$this->assertTrue(c::isCockpit());
		$this->assertEquals('live', c::app()->envByHost());
	}
}
