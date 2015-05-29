<?php

class Controller_api_config_reward extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if( !c::admin()->permission()->check( ['global'] ) ){
			$this->_error();
		}

		switch ( c::getPagePiece( 3 ) ) {
			case 'config':
				$this->_config();
				break;
			case 'config-value':
				$this->_configValue();
				break;
			default:
				$this->_error();
				break;
		}
	}

	private function _configValue(){
		$key = $this->request()[ 'key' ];
		$reward = new Crunchbutton_Reward;
		$settings = $reward->loadSettings();
		if( $settings[ $key ] ){
			echo json_encode( [ 'value' => $settings[ $key ] ] );exit();
		} else {
			$this->_error();
		}
	}

	private function _config(){
		switch ( $this->method() ) {
			case 'post':
				$this->_configSave();
				break;
			default:
				$this->_configExport();
				break;
		}
	}

	private function _configSave(){
		$reward = new Crunchbutton_Reward;
		$settings = $reward->loadSettings();
		foreach( $settings as $key => $value ){
			$config = Crunchbutton_Config::getConfigByKey( $key );
			if( $config->key ){
				$value = trim( $this->request()[ $key ] );
				if( is_numeric( $value ) ){
					$config->set( $value );
					$config->save();
				}
			}
		}
		echo json_encode( [ 'success' => 'success' ] );exit();
	}

	private function _configExport(){
		$out = [];
		$reward = new Crunchbutton_Reward;
		$settings = $reward->loadSettings();
		foreach( $settings as $key => $value ){
			if( is_numeric( $value ) ){
				$value = floatval( $value );
			}
			$out[ $key ] = $value;
		}
		echo json_encode( $out );
		exit;
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}