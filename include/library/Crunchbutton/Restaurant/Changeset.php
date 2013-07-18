<?php

class Crunchbutton_Restaurant_Changeset extends Cana_Table {

	public function restaurant() {
		if (!isset($this->_restaurant)) {
			$this->_restaurant = Restaurant::o($this->id_restaurant);
		}
		return $this->_restaurant;
	}

	public function changes() {
		if (!isset($this->_changes)) {
			$this->_changes = Crunchbutton_Restaurant_Change::q('
				SELECT * FROM restaurant_change
				WHERE
					id_restaurant_change_set="'.$this->id_restaurant_change_set.'"
			');
		}
		return $this->_changes;
	}
	
	public function admin() {
		if (!isset($this->_admin)) {
			$this->_admin = Admin::o($this->id_admin);
		}
		return $this->_admin;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('restaurant_change_set')
			->idVar('id_restaurant_change_set')
			->load($id);
	}
}