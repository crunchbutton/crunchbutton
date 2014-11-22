<?php

class UserTest extends PHPUnit_Framework_TestCase {

	public function testInvite() {
		$code = Crunchbutton_User::inviteCodeGenerator();		
		$match = preg_match('/[qwertyupasdfghjklzxcvbnm]{3}[123456789]{3}[qwertyupasdfghjklzxcvbnm]{3}/i',$code);
		$this->assertTrue($match ? true : false);
	}
}
