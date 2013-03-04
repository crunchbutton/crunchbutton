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

}