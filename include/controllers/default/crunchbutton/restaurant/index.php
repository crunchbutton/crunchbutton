<?php

class Controller_restaurant extends Cana_Controller {
	public function init() {

		$r = Restaurant::o(c::getPagePiece(1));
		Cana::view()->restaurant = $r;
		Cana::view()->display('restaurant/index');
	}
}