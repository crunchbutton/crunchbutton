<?php

class Controller_api_unique extends Crunchbutton_Controller_Rest {
	public function init() {
		switch ( $this->method() ) {
			case 'post':
				$value = $this->request()[ 'value' ];
				if( trim( $value ) == '' ){
					$this->_error();
				}
				switch ( c::getPagePiece( 2 ) ) {
					case 'email':
						$admin = Admin::q( 'SELECT * FROM admin WHERE email = "' . $value . '"' );
						echo json_encode( [ 'canIUse' => ( $admin->count() == 0 ) ] );
						exit;
						break;
					case 'phone':
						$admin = Admin::q( 'SELECT * FROM admin WHERE phone = "' . $value . '"' );
						echo json_encode( [ 'canIUse' => ( $admin->count() == 0 ) ] );
						exit;
						break;
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
