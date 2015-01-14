<?php

class Controller_Api_Marketing extends Crunchbutton_Controller_Rest {
	public function init() {
		$this->_error();
	}
	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}