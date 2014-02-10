<?php

class Controller_api_restaurantHours extends Crunchbutton_Controller_RestAccount {
	
	public function init() {
		
		if (!c::admin()->permission()->check(['global'])) {
			echo json_encode( [ 'error' => 'invalid object' ] );
			exit;
		}
		if( c::getPagePiece( 2 ) ){
			$open_for_business = ( c::getPagePiece( 3 ) == 'close' ? 0 : 1 );
			$restaurant = Restaurant::o( c::getPagePiece( 2 ) );
			if( $restaurant->id_restaurant ){
				$restaurant->open_for_business = $open_for_business;
				$restaurant->save();
				echo json_encode( array( 'success' => true ) );
			} else {
				echo json_encode( [ 'error' => 'invalid object' ] );	
			}
		} else {
			echo json_encode( [ 'error' => 'invalid object' ] );
		}
	}
}
