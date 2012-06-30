<?php

class Crunchbutton_Dish extends Cana_Table {
	public function exports() {
		$out = $this->properties();
		$out['price'] = number_format($out['price'],2);
		foreach ($this->toppings() as $topping) {
			$out['_toppings'][$topping->id_topping] = $topping->exports();
		}
		foreach ($this->substitutions() as $substitution) {
			$out['_substitutions'][$substitution->id_substitution] = $substitution->exports();
		}
		return $out;
	}
	
	public function restaurant() {
		return Restaurant::o($this->id_restaurant);
	}

	public function toppings() {
		if (!isset($this->_toppings)) {
			$this->_toppings = Topping::q('
				select topping.*, dish_topping.default from topping
				left join dish_topping using(id_topping)
				where id_dish="'.$this->id_dish.'"
			');
		}
		return $this->_toppings;
	}

	public function substitutions() {
		if (!isset($this->_substitution)) {
			$this->_substitution = Substitution::q('
				select substitution.*, dish_substitution.default from substitution
				left join dish_substitution using(id_substitution)
				where id_dish="'.$this->id_dish.'"
			');
		}
		return $this->_substitution;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('dish')
			->idVar('id_dish')
			->load($id);
	}
}