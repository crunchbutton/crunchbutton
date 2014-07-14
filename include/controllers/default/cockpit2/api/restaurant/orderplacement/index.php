<?php

class Controller_api_restaurant extends Crunchbutton_Controller_Rest {

	public function init() {

		switch ( $this->method() ) {

			case 'get':

				switch ( c::getPagePiece( 3 ) ) {
					default:
						$restaurant = Admin::restaurantOrderPlacement();
						if( $restaurant ){
							$out = [];
							$out[ 'id_restaurant' ] = $restaurant->id_restaurant;
							$out[ 'name' ] = $restaurant->name;
							$out[ 'address' ] = $restaurant->address;
							$out[ 'lat' ] = $restaurant->loc_lat;
							$out[ 'lon' ] = $restaurant->loc_long;
							$out[ 'range' ] = $restaurant->delivery_radius;
							$out[ 'fee_customer' ] = $restaurant->fee_customer;
							$out[ 'delivery_service_markup' ] = $restaurant->delivery_service_markup;
							$out[ 'delivery_service' ] = $restaurant->delivery_service;
							$out[ 'delivery_fee' ] = $restaurant->delivery_fee;
							$out[ 'fee_customer' ] = $restaurant->fee_customer;
							$out[ 'tax' ] = $restaurant->tax;
							echo json_encode( $out );
						} else {
							$this->_error();
						}
						break;
				}

			break;

			default:
				$this->_error();
			break;
		}

	}
	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}