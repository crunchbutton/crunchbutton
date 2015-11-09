<?php

class Controller_home extends Cana_Controller {
	public function init() {

		if (c::getPagePiece(0)) {
			if (c::getPagePiece(0) != 'food-delivery') {
				$c = Community::permalink(c::getPagePiece(0));

				if ($c->id_community) {
					c::view()->community = $c;
				}
			}

			if ($c->id_community || c::getPagePiece(0) == 'food-delivery' && c::getPagePiece(1)) {
				$r = Restaurant::permalink(c::getPagePiece(1));

				if ($r->id_restaurant) {
					c::view()->restaurant = $r;
				}
			}
		}

		c::view()->display('home/index');
	}
}