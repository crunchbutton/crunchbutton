<?php

class Crunchbutton_Dish_Option_Price extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('dish_option_price')
			->idVar('id_dish_option_price')
			->load($id);
	}
}