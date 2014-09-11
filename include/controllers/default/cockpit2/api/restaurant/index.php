<?php

class Controller_api_restaurant extends Crunchbutton_Controller_Rest {

	public function init() {

		switch ( $this->method() ) {

			case 'get':

				switch ( c::getPagePiece( 2 ) ) {
					// list of restaurants that were already paid
					case 'paid-list':
						$restaurants = Crunchbutton_Restaurant::q( 'SELECT DISTINCT(r.id_restaurant) AS id_restaurant, r.name  FROM restaurant r
																					INNER JOIN payment p ON p.id_restaurant = r.id_restaurant
																				ORDER BY r.name ASC' );
						$export = [];
						$export[] = array( 'id_restaurant' => 0, 'name' => 'All' );
						foreach( $restaurants as $restaurant ){
							$export[] = array( 'id_restaurant' => $restaurant->id_restaurant, 'name' => $restaurant->name );
						}
						echo json_encode( $export );
						break;

					// Simple list returns just the name and id
					case 'list':
						$restaurants = Crunchbutton_Restaurant::active();
						$export = [];
						foreach( $restaurants as $restaurant ){
							$export[] = array( 'id_restaurant' => $restaurant->id_restaurant, 'name' => $restaurant->name );
						}
						echo json_encode( $export );
						break;

					case 'no-payment-method':
						$restaurants = Crunchbutton_Restaurant::with_no_payment_method();
						$export = [];
						foreach( $restaurants as $restaurant ){
							$export[] = array( 'id_restaurant' => $restaurant->id_restaurant, 'name' => $restaurant->name );
						}
						echo json_encode( $export );
						break;
					default:
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