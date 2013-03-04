<?php

class Crunchbutton_Credit extends Cana_Table
{
	const TYPE_CREDIT = 'c';
	const TYPE_DEBIT = 'd';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('promo')
			->idVar('id_promo')
			->load($id);
	}

}