<?php

class Crunchbutton_Balanced_Account extends Cana_Model {
	public static function createSessionEmail() {
		return 'session-'.c::auth()->session()->id_session.'@_DOMAIN_';
	}

	public static function bySession() {
		return self::byEmail(self::createSessionEmail());
	}

	public static function byEmail($email) {
		$account = false;

		try {
			$account = c::balanced()->accounts->query()
				->filter(Balanced\Account::$f->email_address->eq($email))
				->one();
		
		} catch (Exception $e) {

		}
		return $account;
	}
	
	public static function byId($id) {
		$account = Balanced\Account::get(c::balanced()->accounts->uri.'/'.$id);
		return $account;
	}
}