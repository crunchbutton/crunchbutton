<?php

class Controller_restaurantmap extends Cana_Controller {
	public function init() {

		if (c::getPagePiece(1)) {
			$community = Community::permalink(c::getPagePiece(1));
			Cana::view()->restaurants = $community->restaurants();
			Cana::view()->community = $community;

		} else {
			Cana::view()->restaurants = Restaurant::q('select * from restaurant where active=1 and loc_lat');
		}
		
		Cana::view()->layout('layout/blank');
		Cana::view()->display('restaurantmap/index');
	}
}