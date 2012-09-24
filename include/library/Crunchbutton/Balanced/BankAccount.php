<?php

class Crunchbutton_Balanced_BankAccount extends Cana_Model {
	public static function byId($id) {
		$account = Balanced\BankAccount::get(c::balanced()->bank_accounts->uri.'/'.$id);
		return $account;
	}
}