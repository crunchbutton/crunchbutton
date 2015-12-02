<?php

class Crunchbutton_Restaurant_Chain extends Cana_Table{

	public function restaurant(){
		if( !$this->_restaurant ){
			$this->_restaurant = Restaurant::o( $this->id_restaurant );
		}
		return $this->_restaurant;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('restaurant_chain')
			->idVar('id_restaurant_chain')
			->load($id);
	}
}
