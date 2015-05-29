<?php

class Controller_api_config_rules extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if( !c::admin()->permission()->check( ['global'] ) ){
			$this->_error();
		}

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
		$configs = Crunchbutton_Config::q( 'SELECT * FROM config WHERE `key` like "rule-%"' );
		foreach( $configs as $config ){
			$key = str_replace( '-', '_', $config->key );
			if( isset( $this->request()[ $key ] ) ){
				$config->value = $this->request()[ $key ];
				$config->save();
			}
		}
		echo json_encode( [ 'success' => 'success' ] );exit();
	}

	private function _configExport(){
		$out = [];
		$configs = Crunchbutton_Config::q( 'SELECT * FROM config WHERE `key` like "rule-%"' );
		foreach( $configs as $config ){
			$key = str_replace( '-', '_', $config->key );
			$val = ( is_numeric( $config->value ) ? intval( $config->value ) : $config->value );
			$val = is_null( $val ) ? 0 : $val;
			$out[ $key ] = $val;
		}
		echo json_encode( $out );exit;
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}