<?php

class Controller_api_unique extends Crunchbutton_Controller_Rest {

	public function init() {

		switch ( $this->method() ) {
			case 'post':
				$value = $this->request()[ 'value' ];
				$name =  $this->request()[ 'name' ];
				$id_admin =  $this->request()[ 'id_admin' ];
				if( trim( $value ) == '' ){
					$this->_error();
				}
				// See: #3392
				if ( strpos( strtolower( $name ), '[test]' ) !== false ) {
					echo json_encode( [ 'canIUse' => true ] );
					exit;
				} else {
					switch ( c::getPagePiece( 2 ) ) {
						case 'email':
							$admin = Admin::q( 'SELECT * FROM admin WHERE email = "' . $value . '" AND active = true' );
							echo json_encode( [ 'canIUse' => ( $admin->count() == 0 ) ] );
							exit;
							break;

						case 'phone':
							$value = preg_replace( '/[^0-9]/i', '', $value );
							$admin = Admin::q( 'SELECT * FROM admin WHERE phone = "' . $value . '" AND id_admin != "' . $id_admin . '" AND active = true' );
							echo json_encode( [ 'canIUse' => ( $admin->count() == 0 ) ] );
							exit;
							break;

						case 'login':
							$admin = Admin::q( 'SELECT * FROM admin WHERE login = "' . $value . '" AND id_admin != "' . $id_admin . '" AND active = true' );
							echo json_encode( [ 'canIUse' => ( $admin->count() == 0 ) ] );
							exit;
							break;
					}
				}
				break;
		}

		$this->_error();
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}
