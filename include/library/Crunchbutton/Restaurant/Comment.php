<?php

class Crunchbutton_Restaurant_Comment extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('restaurant_comment')
			->idVar('id_restaurant_comment')
			->load($id);
	}
	
	public function user() {
		if (!isset($this->_user)) {
			$this->_user = User::o($this->id_user);
		}
		return $this->_user;
	}
	
	public function restaurant() {
		if (!isset($this->_restaurant)) {
			$this->_restaurant = Restaurant::o($this->id_restaurant);
		}
		return $this->_restaurant;
	}	
}