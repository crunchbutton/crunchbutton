<?php

class Controller_api_config_processor extends Crunchbutton_Controller_Rest {
	public function init() {
		if( !c::admin()->permission()->check( ['global', 'settlement' ] ) ){
			$this->_error();
		}

		$config[ 'processor' ][ 'type' ] = Crunchbutton_User_Payment_Type::processor();
			$config[ 'processor' ][ 'stripe' ] = c::config()->stripe->{c::getEnv()}->{'public'};
			$config[ 'processor' ][ 'balanced' ] = c::balanced()->href;
			echo json_encode( $config );exit();

	}
	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}
