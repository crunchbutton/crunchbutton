<?php

class Controller_api_driver_notify extends Crunchbutton_Controller_RestAccount {
	
	public function init() {

		if( $this->method() != 'post' ){
			$this->_error();
		}

		$id_admin = c::getPagePiece( 3 );

		$driver = Crunchbutton_Admin::o( $id_admin );

		if( !$driver->id_admin ){
			$this->_error();
		}

		$message = $this->request()[ 'message' ];

		Log::debug( [ 'action' => 'notification started', 'id_admin' => $id_admin, 'message' => $message, 'type' => 'drivers-onboarding'] );

		$notify = Cockpit_Driver_Notify::send( $driver->id_admin, $message );


		if( $notify && $notify[ 'success' ] ){
			echo json_encode( $notify );
		} else {
			$this->_error( $notify[ 'error' ] );
		}
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}