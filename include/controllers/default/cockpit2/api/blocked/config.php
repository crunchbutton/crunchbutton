<?php

class Controller_Api_Blocked_Config extends Crunchbutton_Controller_Rest {

	public function init() {

		$this->_permissionDenied();

		if ( $this->method() == 'post' ) {
			$message = $this->request()[ 'message' ];
			if( $message ){
				Crunchbutton_Blocked::updateMessage( $message );
				echo json_encode( [ 'success' => $message ] );exit();
			} else {
				echo json_encode( [ 'error' => 'message can not be empty' ] );exit();
			}
		} else {
			$message = Crunchbutton_Blocked::getMessage();
			echo json_encode( [ 'message' => $message ] );exit();
		}

	}
	private function _permissionDenied(){
		if (!c::admin()->permission()->check(['global', 'customer-all', 'customer-block' ])) {
			$this->error(401, true);
		}
	}
}
