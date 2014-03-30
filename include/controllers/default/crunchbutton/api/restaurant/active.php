<?php

class Controller_api_restaurant_active extends Crunchbutton_Controller_Rest {
	public function init() {
		$r = Restaurant::o( c::getPagePiece( 3 ) );
		if( !$r->id_restaurant ){
			echo json_encode( [ 'error' => 'invalid object' ] );
			exit;
		}
		echo json_encode( [ 'active' => ( $r->active > 0 ? true : false ) ] );
	}
}