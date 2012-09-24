<?php

class Controller_api_test extends Crunchbutton_Controller_Rest {
	public function init() {

		$r = Restaurant::o(11);
		$r->createMerchant([
			'name' => 'Devin Smith',
			'zip' => '90292',
			'address' => '13701 marina pointe drive',
			'dob' => '1984-09'
		]);
	exit;
//		$r = Restaurant::o(1);
//		$r->saveBankInfo('321174851','1234567890','test');
		$p = Payment::credit([
			'id_restaurant' => 1,
			'amount' => 5.00,
			'note' => 'another test'
		]);

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