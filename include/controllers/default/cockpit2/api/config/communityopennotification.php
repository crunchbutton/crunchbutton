<?php

class Controller_api_config_Communityopennotification extends Crunchbutton_Controller_RestAccount {

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
		$keys = Crunchbutton_Community_Notification::openByDriverNotifyConfig();
		foreach ($keys as $key => $value) {
			$value = $this->request()[$key];
			$config = Crunchbutton_Config::getConfigByKey($key);
			if(!$config->id_config){
				$config = new Config;
				$config->key = $key;
			}
			$config->value = $value;
			$config->save();
		}
		echo json_encode( [ 'success' => 'success' ] );exit();
	}

	private function _configExport(){
		$keys = Crunchbutton_Community_Notification::openByDriverNotifyConfig();
		echo json_encode($keys);exit;
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}