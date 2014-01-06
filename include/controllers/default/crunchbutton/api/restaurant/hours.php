<?php

class Controller_api_restaurant_hours extends Crunchbutton_Controller_Rest {
	public function init() {
		$r = Restaurant::o( c::getPagePiece( 3 ) );
		echo $r->export_hours();
	}
}