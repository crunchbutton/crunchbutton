<?php

class Crunchbutton_Balanced_Account extends Cana_Model {
	public static function createSessionEmail() {
		return 'session-'.c::auth()->session()->adapter()->id_session.'@_DOMAIN_';
	}

	public static function bySession() {
		return self::byEmail(self::createSessionEmail());
	}

	public static function byEmail($email) {
		try {
			$account = c::balanced()->customers->query()
				->filter(Balanced\Customer::$f->email_address->eq($email))
				->one();

		} catch (Exception $e) {
			$account = null;
		}
		return $account;
	}

	public static function byId($id) {
		try {
			$account = Balanced\Customer::get(c::balanced()->customers->uri.'/'.$id);
		} catch (Exception $e) {
			// invalid account. most likly due to mismatched dev and live keys
			$account = null;
		}
		return $account;
	}
}
