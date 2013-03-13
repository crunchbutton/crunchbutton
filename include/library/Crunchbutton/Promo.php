<?php

class Crunchbutton_Promo extends Cana_Table
{

	const TYPE_SHARE = 'user_share';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('promo')
			->idVar('id_promo')
			->load($id);
	}
	public function user() {
		return User::o($this->id_user);
	}
	public function restaurant() {
		return Restaurant::o($this->id_restaurant);
	}
}