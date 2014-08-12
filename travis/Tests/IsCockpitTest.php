<?php

class IsCockpitTest extends PHPUnit_Framework_TestCase {
	public function testCockpit() {
		$_SERVER['HTTP_HOST'] = 'cockpit.la';
		$this->assertTrue(c::isCockpit());
	}
	public function testCockpitOld() {
		$_SERVER['HTTP_HOST'] = 'cockpit._DOMAIN_';
		$this->assertTrue(c::isCockpit());
	}
	public function testCockpitBeta() {
		$_SERVER['HTTP_HOST'] = 'beta.cockpit.la';
		$this->assertTrue(c::isCockpit());
	}
	public function testCockpitLocal() {
		$_SERVER['HTTP_HOST'] = 'cockpit3.localhost';
		$this->assertTrue(c::isCockpit());
	}
	public function testCockpitLocalOld() {
		$_SERVER['HTTP_HOST'] = 'cockpit.localhost';
		$this->assertTrue(c::isCockpit());
	}
	public function testCockpitUser() {
		$_SERVER['HTTP_HOST'] = 'cockpit3.kenneth.crunchr.co';
		$this->assertTrue(c::isCockpit());
	}
	public function testCockpitUserOld() {
		$_SERVER['HTTP_HOST'] = 'cockpit.kenneth.crunchr.co';
		$this->assertTrue(c::isCockpit());
	}
	public function testCockpitUserBeta() {
		$_SERVER['HTTP_HOST'] = 'beta.cockpit3.kenneth.crunchr.co';
		$this->assertTrue(c::isCockpit());
	}
	public function testCockpitUserBetaOld() {
		$_SERVER['HTTP_HOST'] = 'beta.cockpit.kenneth.crunchr.co';
		$this->assertTrue(c::isCockpit());
	}
	
	
	public function testHome() {
		$_SERVER['HTTP_HOST'] = '_DOMAIN_';
		$this->assertFalse(c::isCockpit());
	}
	public function testHomeBeta() {
		$_SERVER['HTTP_HOST'] = 'beta.crunchr.co';
		$this->assertFalse(c::isCockpit());
	}
	public function testHomeStaging() {
		$_SERVER['HTTP_HOST'] = 'staging.crunchr.co';
		$this->assertFalse(c::isCockpit());
	}
	public function testHomeLocal() {
		$_SERVER['HTTP_HOST'] = 'crunchbutton.localhost';
		$this->assertFalse(c::isCockpit());
	}
	public function testHomeUserBeta() {
		$_SERVER['HTTP_HOST'] = 'beta.kenneth.crunchr.co';
		$this->assertFalse(c::isCockpit());
	}
	public function testHomeUser() {
		$_SERVER['HTTP_HOST'] = 'kenneth.crunchr.co';
		$this->assertFalse(c::isCockpit());
	}
	

}