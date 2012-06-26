<?php

class Crunchbutton_Restaurant extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('restaurant')
			->idVar('id_restaurant')
			->load($id);
	}
}