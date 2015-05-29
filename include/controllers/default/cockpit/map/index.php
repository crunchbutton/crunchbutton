<?php

class Controller_map extends Cana_Controller {
	public function init() {

		if (c::getPagePiece(1)) {
			$community = Community::permalink(c::getPagePiece(1));
			Cana::view()->restaurants = $community->restaurantByLoc();

		} else {
			Cana::view()->restaurants = Restaurant::q('select * from restaurant where active=true and loc_lat');
		}
		
		Cana::view()->layout('layout/blank');
		Cana::view()->useFilter(false);
		Cana::view()->display('map/index');
	}
}