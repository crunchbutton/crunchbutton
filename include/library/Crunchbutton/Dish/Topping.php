<?php

class Crunchbutton_Dish_Topping extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('dish_topping')
			->idVar('id_dish_topping')
			->load($id);
	}
}