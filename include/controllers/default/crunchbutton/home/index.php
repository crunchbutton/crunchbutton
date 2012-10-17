<?php

class Controller_home extends Cana_Controller {
	public function init() {
		if (c::getPagePiece(0)) {
			$c = Community::permalink(c::getPagePiece(0));

			if ($c->id_community) {
				if (c::getPagePiece(1)) {

					$r = Restaurant::permalink(c::getPagePiece(1));
					if ($r->id_restaurant) {
						Cana::view()->restaurant = $r;
					}
				}
				Cana::view()->community = $c;
			}
		}
		Cana::view()->display('home/index');
	}
}