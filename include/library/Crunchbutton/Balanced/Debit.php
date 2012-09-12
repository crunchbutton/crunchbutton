<?php

class Crunchbutton_Balanced_Debit extends Cana_Model {
	public static function byId($id) {
		$account = Balanced\Account::get(c::balanced()->debits->uri.'/'.$id);
		return $account;
	}
}