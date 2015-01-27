<?php

class Crunchbutton_Dish_Option_Change extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('dish_option_change')
			->idVar('id_dish_option_change')
			->load($id);
	}
}