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
		$order = Order::q('select * from `order` where id_restaurant="'.$this->id_restaurant.'" and id_user="'.$this->id_user.'" order by id_order desc limit 0,1' );
		$out['delivery_type'] = $order->delivery_type;
		$out['pay_type'] = $order->pay_type;
		foreach ($this->dishes() as $dish) {
			$out['_dishes'][$dish->id_order_dish] = $dish->exports();
		}
		return $out;
	}
	
	public static function cloneFromOrder($order) {
		$preset = new Preset;
		$preset->id_restaurant = $order->id_restaurant;
		$preset->id_user = $order->user()->id_user;
		$preset->notes = $order->notes;
		$preset->save();
		
		foreach ($order->dishes() as $d) {
			$dish = new Order_Dish;
			$dish->id_dish = $d->id_dish;
			$dish->id_preset = $preset->id_preset;
			$dish->save();

			foreach ($d->options() as $o) {
				$option = new Order_Dish_Option;
				$option->id_option = $o->id_option;
				$option->id_order_dish = $dish->id_order_dish;
				$option->save();
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