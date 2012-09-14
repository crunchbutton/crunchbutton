<?php

class Crunchbutton_Dish_Option extends Cana_Table {
	public function exports() {
		$out = $this->properties();

		foreach ($this->prices() as $price) {
			$out['prices'][$price->id_dish_option_price] = $price->exports();
		}

		return $out;
	}
	
	public function prices() {
		if (!isset($this->_prices)) {
			$this->_prices = Dish_Option_Price::q('select * from dish_option_price where id_option="'.$this->id_option.'"');
		}
		return $this->_prices;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('dish_option')
			->idVar('id_dish_option')
			->load($id);
	}
}