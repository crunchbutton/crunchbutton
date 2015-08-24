<?php

class Controller_api_config_menu extends Crunchbutton_Controller_RestAccount {

	const LABEL_1_KEY = 'menu_item_1_label';
	const URL_1_KEY = 'menu_item_1_url';

	public function init() {
		if( !c::admin()->permission()->check( ['global'] ) ){
			$this->_error();
		}
		$this->_config();
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
		$keys = [ static::LABEL_1_KEY, static::URL_1_KEY ];
		foreach( $keys as $key ){
			$value = $this->request()[ $key ];
			$config = Crunchbutton_Config::getConfigByKey( $key );
			$config->set( $value );
			$config->save();
		}
		echo json_encode( [ 'success' => 'success' ] );exit();
	}

	private function _configExport(){
		$out = [];
		$out[ static::LABEL_1_KEY ] = Crunchbutton_Config::getVal( static::LABEL_1_KEY );
		$out[ static::URL_1_KEY ] = Crunchbutton_Config::getVal( static::URL_1_KEY );
		echo json_encode( $out );exit;
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}