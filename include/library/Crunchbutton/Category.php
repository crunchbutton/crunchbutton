<?php

class Crunchbutton_Category extends Cana_Table {
	public function restaurant() {
		return Restaurant::o($this->id_restaurant);
	}
	
	public function dishes() {
		if (!isset($this->_dishes)) {
			$this->_dishes = Dish::q('select * from dish where id_category="'.$this->id_category.'"');
		}
		return $this->_dishes;
	}
	
	public function exports() {
		$out = $this->properties();
		foreach ($this->dishes() as $dish) {
			$out['_dishes'][$dish->id_dish] = $dish->exports();
		}
		return $out;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('category')
			->idVar('id_category')
			->load($id);
	}
}