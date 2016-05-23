<?php

class Controller_api_config_pexcard extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if( !c::admin()->permission()->check( ['global', 'settlement', 'support-all', 'community-cs' ] ) ){
			$this->_error();
		}

		switch ( c::getPagePiece( 3 ) ) {
			case 'config':
				$this->_config();
				break;
			case 'config-value':
				$this->_configValue();
				break;
			case 'add-business':
				$this->_addBusiness();
				break;
			case 'remove-business':
				$this->_removeBusiness();
				break;
			case 'add-test':
				$this->_addTest();
				break;
			case 'cards':
				$this->_cards();
				break;
			case 'remove-test':
				$this->_removeTest();
				break;
			default:
				$this->_configExport();
				break;
		}
	}

	private function _configValue(){
		$key = $this->request()[ 'key' ];
		$pexcard = new Cockpit_Admin_Pexcard;
		$settings = $pexcard->loadSettings();
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

	private function _addBusiness(){
		if( intval( $this->request()[ 'serial' ] ) ){
			$config = new Crunchbutton_Config;
			$config->id_site = null;
			$config->key = Cockpit_Admin_Pexcard::CONFIG_KEY_PEX_BUSINESS_CARD;
			$config->exposed = 0;
			$config->value = intval( $this->request()[ 'serial' ] );
			$config->save();
		}
		$this->_configExport();
	}

	private function _removeBusiness(){
		if( intval( $this->request()[ 'id_config' ] ) ){
			$config = Crunchbutton_Config::o( $this->request()[ 'id_config' ] );
			if( $config->id_config ){
				$config->delete();
			}
		}
		$this->_configExport();
	}

	private function _addTest(){
		if( intval( $this->request()[ 'serial' ] ) ){
			$config = new Crunchbutton_Config;
			$config->id_site = null;
			$config->key = Cockpit_Admin_Pexcard::CONFIG_KEY_PEX_TEST_CARD;
			$config->exposed = 0;
			$config->value = intval( $this->request()[ 'serial' ] );
			$config->save();
		}
		$this->_configExport();
	}

	private function _removeTest(){
		if( intval( $this->request()[ 'id_config' ] ) ){
			$config = Crunchbutton_Config::o( $this->request()[ 'id_config' ] );
			if( $config->id_config ){
				$config->delete();
			}
		}
		$this->_configExport();
	}

	private function _configSave(){
		$pexcard = new Cockpit_Admin_Pexcard;
		$settings = $pexcard->loadSettings();
		foreach( $settings as $key => $value ){
			$config = Crunchbutton_Config::getConfigByKey( $key );
			if( $config->key ){
				$value = trim( $this->request()[ $key ] );
				// if( $value ){
					$config->set( $value );
					$config->save();
				// }
			}
		}
		echo json_encode( [ 'success' => 'success' ] );exit();
	}

	private function _configExport(){
		$out = [];
		$pexcard = new Cockpit_Admin_Pexcard;
		$settings = $pexcard->loadSettings();
		foreach( $settings as $key => $value ){
			if( is_numeric( $value ) ){
				$value = floatval( $value );
			} else if (is_bool($value)) {
			} else if (is_array($value)) {
			} else {
				$value = 0;
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