<?php

class Crunchbutton_Dish_Option extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('dish_option')
			->idVar('id_dish_option')
			->load($id);
	}
}