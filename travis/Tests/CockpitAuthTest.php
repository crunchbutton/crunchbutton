<?php

/**
 * test authentication and keep it in the db for the next tests
 */

class CockpitAuthTest extends PHPUnit_Framework_TestCase {

	protected static $login = 'root';
	protected static $password = 'password';

	public static function setUpBeforeClass() {

		if (!$_ENV['TRAVIS']) {
			$a = Admin::login(self::$login);
		}

		if (!$a || !$a->id_admin) {
			$a = new Admin([
				'name' => self::$login,
				'login' => self::$login,
				'active' => true,
				'pass' => Crunchbutton_Admin_Auth::passwordEncrypt(self::$password)
			]);
			$a->save();

			$p = new Crunchbutton_Admin_Permission([
				'id_admin' => $a->id_admin,
				'permission' => 'global',
				'allow' => true
			]);
			$p->save();
		}
	}

	public function testAuth() {
		$res = c::auth()->doAuthByLocalUser(['email' => self::$login, 'password' => self::$password]);
		$this->assertTrue($res ? true : false);
		$this->assertTrue(c::admin()->id_admin ? true : false);
		$this->assertTrue(c::user()->id_admin ? true : false);
	}
}

