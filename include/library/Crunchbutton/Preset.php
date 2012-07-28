<?php

class Crunchbutton_Preset extends Cana_Table {
	public function dishes() {
		if (!isset($this->_dishes)) {
			$this->_dishes = Order_Dish::q('select * from order_dish where id_preset="'.$this->id_preset.'"');
		}
		return $this->_dishes;
	}

	public function exports() {
		$out = $this->properties();
		foreach ($this->dishes() as $dish) {
			$out['_dishes'][$dish->id_order_dish] = $dish->exports();
		}
		return $out;
	}
	
	public static function cloneFromOrder($order) {
		$preset = new Preset;
		$preset->id_restaurant = $order->id_restaurant;
		$preset->id_user = $order->user()->id_user;
		$preset->save();
		
		foreach ($preset->dishes() as $dish) {
			$dish = clone $dish;
			$dish->id = $dish->id_order_dish = null;
			$dish->id_order = null;
			$dish->id_preset = $preset->id_preset;
			$dish->save(1);

			foreach ($dish->options() as $option) {
				$option = clone $option;
				$option->id = $option->id_order_dish_option = null;
				$option->id_order_dish = $dish->id_order_dish;
				$option->save(1);
			}
		}

		return $preset;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('preset')
			->idVar('id_preset')
			->load($id);
	}
}