<?php

class Crunchbutton_Community extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('restaurant_community')
			->idVar('id_restaurant_community')
			->load($id);
	}
}