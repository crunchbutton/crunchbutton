<?php

class Crunchbutton_Dish_Price extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('dish_price')
			->idVar('id_dish_price')
			->load($id);
	}
}