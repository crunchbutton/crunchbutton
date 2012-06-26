<?php

class Controller_home extends Cana_Controller {
	public function init() {
		$r = Restaurant::q('select * from restaurant where active=1');
		Cana::view()->restaurants = $r;
		Cana::view()->display('home/index');
	}
}