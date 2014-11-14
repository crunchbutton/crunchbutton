<?php

class UserTest extends PHPUnit_Framework_TestCase {

	public function testInvite() {
		$code = Crunchbutton_User::inviteCodeGenerator();		
		$match = preg_match('/[qwertyuiopasdfghjklzxcvbnm]{3}[123456789]{3}[qwertyuiopasdfghjklzxcvbnm]{3}/i',$code);

		$this->assertTrue($match);
	}
}
