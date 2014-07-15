<?php

class Controller_api_restaurant extends Crunchbutton_Controller_Rest {

	public function init() {

		switch ( $this->method() ) {

			case 'get':

				switch ( c::getPagePiece( 3 ) ) {

					case 'status':

						if( is_numeric( c::getPagePiece( 4 ) ) && c::admin()->permission()->check( [ 'global' ] ) ){
							$restaurant = Restaurant::o( intval( c::getPagePiece( 4 ) ) );
						}

						if( !$restaurant->id_restaurant ){
							$restaurant = Admin::restaurantOrderPlacement();
						}

						if( $restaurant ){
							$out = [];
							$out[ 'id_restaurant' ] = intval( $restaurant->id_restaurant );
							$out[ 'peding' ] = ( $restaurant->numberOfOrdersByStatus( Crunchbutton_Order_Action::DELIVERY_ACCEPTED ) + $restaurant->numberOfOrdersByStatus( false ) );
							$out[ 'transit' ] = intval( $restaurant->numberOfOrdersByStatus( Crunchbutton_Order_Action::DELIVERY_PICKEDUP ) );
							$phone = c::config()->twilio->live->outgoingCustomer;
							if( preg_match( '/^\+\d(\d{3})(\d{3})(\d{4})$/', $phone, $matches ) ){
    						$phone = $matches[1] . '-' .$matches[2] . '-' . $matches[3];
							}
							$out[ 'phone_formatted' ] = $phone;
							$out[ 'phone' ] = c::config()->twilio->live->outgoingCustomer;
							echo json_encode( $out );exit;
						} else {
							$this->_error();
						}

						break;

					case 'all':
						$out = [];
						$restaurants = Restaurant::q( 'SELECT * FROM restaurant WHERE active_restaurant_order_placement = 1 ORDER BY name ASC' );
						foreach( $restaurants as $restaurant ){
							$out[] = [ 'id_restaurant' => intval( $restaurant->id_restaurant ), 'name' => $restaurant->name ];
						}
						echo json_encode( $out );exit;
						break;

					default:

						if( is_numeric( c::getPagePiece( 3 ) ) && c::admin()->permission()->check( [ 'global' ] ) ){
							$restaurant = Restaurant::o( intval( c::getPagePiece( 3 ) ) );
						}

						if( !$restaurant->id_restaurant ){
							$restaurant = Admin::restaurantOrderPlacement();
						}

						if( $restaurant ){
							$out = [];
							$out[ 'id_restaurant' ] = intval( $restaurant->id_restaurant );
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