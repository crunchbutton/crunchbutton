<?php

class Controller_api_driver_log extends Crunchbutton_Controller_RestAccount {
	
	public function init() {
		if( c::getPagePiece( 3 ) ){
			$admin = Crunchbutton_Admin::o( c::getPagePiece( 3 ) );
			if( $admin->id_admin ){
				$logs = Crunchbutton_Driver_Log::AllByDriver( $admin->id_admin );
				$list = [];
				foreach( $logs as $log ){
					// lets save bandwidth
					unset( $log[ 'id_driver_log' ] );
					unset( $log[ 'id_admin' ] );
					unset( $log[ 'info' ] );
					unset( $log[ 'id' ] );
					unset( $log[ 'datetime' ] );
					$list[] = $log;
				}
				echo json_encode( $list );
			} else {
				echo $this->_error();	
			}
		} else {
			echo $this->_error();
		}
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}