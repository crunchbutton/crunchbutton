<?php

class Crunchbutton_Topping extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('topping')
			->idVar('id_topping')
			->load($id);
	}
}