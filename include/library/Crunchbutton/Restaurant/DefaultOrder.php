<?php

class Crunchbutton_Restaurant_DefaultOrder extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('restaurant_default_order')
			->idVar('id_restaurant_default_order')
			->load($id);
	}
}