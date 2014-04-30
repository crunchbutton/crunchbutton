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
                             	login="'.c::db()->escape($email).'"
                                AND pass="'.c::db()->escape($password).'"
                                AND active=1
                                LIMIT 1';
		return Admin::q($query)->get(0);

	}

}