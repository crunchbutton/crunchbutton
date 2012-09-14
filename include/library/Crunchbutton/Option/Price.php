<?php

class Crunchbutton_Option_Price extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('option_price')
			->idVar('id_option_price')
			->load($id);
	}
}