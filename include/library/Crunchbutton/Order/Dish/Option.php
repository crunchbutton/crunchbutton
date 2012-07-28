<?php

class Crunchbutton_Order_Dish_Option extends Cana_Table {
	public function option() {
		return Option::q('select * from `option` where id_option="'.$this->id_option.'"');
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('order_dish_option')
			->idVar('id_order_dish_option')
			->load($id);
	}
}