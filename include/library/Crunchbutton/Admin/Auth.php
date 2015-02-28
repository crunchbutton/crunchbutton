<?php

class Crunchbutton_Admin_Auth extends Cana_Model {

	public static function passwordEncrypt($password) {
		return sha1(c::crypt()->encrypt($password));
	}

	public static function localLogin($email, $password) {
		$password = self::passwordEncrypt($password);
		$query = '
			SELECT *
			FROM admin
			WHERE
				login=:email
				AND pass=:password
				AND active=true
				LIMIT 1
		';
		return Admin::q($query, ['email' => $email, 'password' => $password])->get(0);

	}

}