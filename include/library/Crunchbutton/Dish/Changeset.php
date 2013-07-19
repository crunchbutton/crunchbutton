<?php

class Crunchbutton_Dish_Changeset extends Cana_Table {

	public function restaurant() {
		if (!isset($this->_dish)) {
			$this->_dish = Dish::o($this->id_dish);
		}
		return $this->_dish;
	}

	public function changes() {
		if (!isset($this->_changes)) {
			$this->_changes = Crunchbutton_Dish_Change::q('
				SELECT * FROM dish_change
				WHERE
					id_dish_change_set="'.$this->id_dish_change_set.'"
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
			->table('dish_change_set')
			->idVar('id_dish_change_set')
			->load($id);
	}
}