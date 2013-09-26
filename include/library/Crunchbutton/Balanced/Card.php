<?php

class Crunchbutton_Balanced_Card extends Cana_Model {
	public static function byId($id) {
		$account = Balanced\Card::get(c::balanced()->cards->uri.'/'.$id);
		return $account;
	}
}