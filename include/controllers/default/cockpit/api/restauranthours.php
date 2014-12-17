<?php

class Controller_api_restaurantHours extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if ( !c::admin()->permission()->check( [ 'global','community-all', 'community-restaurants' ] ) ) {
			echo json_encode( [ 'error' => 'invalid object' ] );
			exit;
		}
		if( c::getPagePiece( 2 ) ){
			$open_for_business = ( c::getPagePiece( 3 ) == 'close' ? 0 : 1 );
			$restaurant = Restaurant::o( c::getPagePiece( 2 ) );
			if( $restaurant->id_restaurant ){
				$restaurant->open_for_business = $open_for_business;
				$force_close_tagline = $this->request()[ 'force_close_tagline' ];;
				if( $force_close_tagline ){
					$restaurant->force_close_tagline = $force_close_tagline;
				} else {
					$restaurant->force_close_tagline = '';
				}
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
