<?php

class Crunchbutton_Admin_Auth extends Cana_Model {

	public static function passwordEncrypt($password) {
		return sha1(c::crypt()->encrypt($password));
	}

	public static function localLogin($email, $password) {
		$password = self::passwordEncrypt($password);
		$query = sprintf('
			SELECT * 
			FROM admin
			WHERE
				login="%s"
				AND pass="%s"
				AND active=1
				LIMIT 1',
			@mysql_real_escape_string($email),
			@mysql_real_escape_string($password)
		);

		return Admin::q($query)->get(0);

	}

}