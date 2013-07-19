<?php

class Crunchbutton_Dish_Change extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('dish_change')
			->idVar('id_dish_change')
			->load($id);
	}
}