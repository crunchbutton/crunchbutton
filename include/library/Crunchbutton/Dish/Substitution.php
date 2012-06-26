<?php

class Crunchbutton_Dish_Substitution extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('dish_substitution')
			->idVar('id_dish_substitution')
			->load($id);
	}
}