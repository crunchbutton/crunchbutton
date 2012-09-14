<?php

class Crunchbutton_Balanced_Debit extends Cana_Model {
	public static function byId($id) {
		$account = Balanced\Debit::get(c::balanced()->debits->uri.'/'.$id);
		return $account;
	}
}