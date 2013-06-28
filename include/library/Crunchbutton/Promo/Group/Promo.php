<?php

class Crunchbutton_Promo_Group_Promo extends Cana_Table
{
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('promo_group_promo')
			->idVar('id_promo_group_promo')
			->load($id);
	}
}