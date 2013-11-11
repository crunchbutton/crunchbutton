<?php

class Crunchbutton_Restaurant_Hour_Override extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('restaurant_hour_override')
			->idVar('id_restaurant_hour_override')
			->load($id);
	}
}