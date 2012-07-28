<?php

class Crunchbutton_Order_Dish extends Cana_Table {
	public function options() {
		if (!isset($this->_options)) {
			$this->_options = Order_Dish_Option::q('select * from order_dish_option where id_order_dish="'.$this->id_order_dish.'"');
		}
		return $this->_options;
	}
	
	public function dish() {
		return Dish::q('select * from dish where id_dish="'.$this->id_dish.'"');
	}
	
	public function exports() {
		$out = $this->properties();
		foreach ($this->options() as $option) {
			$out['_options'][$option->id_order_dish_option] = $option->exports();
		}
		return $out;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('order_dish')
			->idVar('id_order_dish')
			->load($id);
	}
}