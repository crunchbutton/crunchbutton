<?php

class Crunchbutton_Restaurant_Change extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('restaurant_change')
			->idVar('id_restaurant_change')
			->load($id);
	}
}