<?php

class Controller_api_restaurant_payinfo extends Crunchbutton_Controller_RestAccount {

	public function init() {

		$hasPermission = ( c::admin()->permission()->check( ['global'] ) );

		if( !$hasPermission ){
			$this->_error( 'invalid object' );
		}

		$action = c::getPagePiece( 3 );

		$id_restaurant = c::getPagePiece( 4 );

		$restaurant = Restaurant::o( $id_restaurant );

		if( !$restaurant->id_restaurant ){
			$this->_error();
		}


		switch ( $action ) {
			case 'payment-method':
				$payment_method = $restaurant->payment_type();
				echo json_encode( $payment_method->exports() );exit;
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