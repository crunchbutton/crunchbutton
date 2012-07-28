<?php

class Controller_api_test extends Crunchbutton_Controller_Rest {
	public function init() {
		Dish_Substitution::q('select * from dish_substitution')->each(function() {
			echo $this->name;
			$d = new Dish_Topping;
			$d->id_restaurant = $this->id_restaurant;
			$d->name = $this->name;
			$d->price = $this->price;

			//$d->save();
		});
		//$o = new Order(111);
		//$o->notify();
	}
}