<?php

class Controller_api_test extends Crunchbutton_Controller_Rest {
	public function init() {
		exit;
		$q = 'select dish_option.* from dish_option left join dish using(id_dish) where dish.id_restaurant="18" and dish_option.id_dish="126"';
		$r = c::db()->query($q);
		while ($o = $r->fetch()) {

			$ob = new Dish_Option;
			$ob->id_dish = 125;
			$ob->id_option = $o->id_option;
			$ob->default = $o->default;
			$ob->save();
		}

		//$o = new Order(111);
		//$o->notify();
	}
}