<?php
//die('wasssuppp');
class Crunchbutton_Dish_Option_Changeset extends Cana_Table {

	public function dish_option() {
		if (!isset($this->_dish_option)) {
			$this->_dish_option = Dish_Option::o($this->id_dish_option);
		}
		return $this->_dish_option;
	}

	public function changes() {
		if (!isset($this->_changes)) {
			$this->_changes = Crunchbutton_Dish_Option_Change::q('
				SELECT * FROM dish_option_change
				WHERE
					id_dish_option_change_set="'.$this->id_dish_option_change_set.'"
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
			->table('dish_option_change_set')
			->idVar('id_dish_option_change_set')
			->load($id);
	}
}