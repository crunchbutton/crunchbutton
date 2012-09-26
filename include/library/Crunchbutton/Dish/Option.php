<?php

class Crunchbutton_Dish_Option extends Cana_Table {
	public function byDishOption($dish, $option) {

	}
	
	public function delete() {
		parent::delete();

		$odo = Order_Dish_Option::q('
			select order_dish_option.* from order_dish_option
			left join order_dish on order_dish.id_order_dish=order_dish_option.id_order_dish
			where order_dish_option.id_option="'.$this->id_option.'"
		');
		$do = Dish_Option::q('select * from dish_option where id_option="'.$this->id_option.'"');

		if (!$odo->count() && !$do->count()) {
			$this->option()->delete();
		}
	}
	
	public function option() {
		return Option::o($this->id_option);
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('dish_option')
			->idVar('id_dish_option')
			->load($id);
	}
}