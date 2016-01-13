<?php

class Controller_api_shifts_settings extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if( !c::admin()->permission()->check( ['global', 'support-all', 'support-view', 'support-crud' ] ) ){
			$this->error( 401 );
		}

		if( $this->method() == 'post' ){
			$this->_save();
		} else {
			$this->_load();
		}
	}

	private function _save(){
		$value = $this->request()[ Crunchbutton_Community_Shift::CREATE_DRIVER_SHIFT_BUFFER_KEY ];
		Crunchbutton_Config::store( Crunchbutton_Community_Shift::CREATE_DRIVER_SHIFT_BUFFER_KEY, $value, null );
		echo json_encode( [ 'success' => true ] );exit;
	}

	private function _load(){
		echo json_encode( [ Crunchbutton_Community_Shift::CREATE_DRIVER_SHIFT_BUFFER_KEY => Crunchbutton_Community_Shift::driverBufferBeforeCreateShift() ] );exit;;
	}

}
