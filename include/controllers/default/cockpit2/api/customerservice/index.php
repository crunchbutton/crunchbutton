<?php

class Controller_api_customerservice extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (!c::admin()->permission()->check(['global'])) {
			$this->error(401, true);
		}

		switch ( $this->method() ) {
			case 'post':
				switch ( c::getPagePiece( 2 ) ) {
					case 'config':
						$this->_configSave();
						break;
				}
			case 'get':
				switch ( c::getPagePiece( 2 ) ) {
					case 'config':
						$this->_configExports();
						break;
				}
			break;
			default:
				echo json_encode( [ 'error' => 'invalid object' ] );
			break;
		}
	}

	private function _configSave(){
		$cs_config_call_driver = $this->request()[ 'cs_config_call_driver' ];
		$cs_config_call_default_phone = $this->request()[ 'cs_config_call_default_phone' ];
		$callDriver = Crunchbutton_Config::getConfigByKey(Crunchbutton_Support::CONFIG_KEY_CS_CALL_DRIVER);
		if(!$callDriver->id_config){
			$callDriver = new Crunchbutton_Config;
			$callDriver->key = Crunchbutton_Support::CONFIG_KEY_CS_CALL_DRIVER;
		}
		$callDriver->value = $cs_config_call_driver;
		$callDriver->save();

		$callDefaultPhone = Crunchbutton_Config::getConfigByKey(Crunchbutton_Support::CONFIG_KEY_CS_CALL_DEFAULT_PHONE);
		if(!$callDefaultPhone->id_config){
			$callDefaultPhone = new Crunchbutton_Config;
			$callDefaultPhone->key = Crunchbutton_Support::CONFIG_KEY_CS_CALL_DEFAULT_PHONE;
		}
		$callDefaultPhone->value = $cs_config_call_default_phone;
		$callDefaultPhone->save();
		return $this->_configExports();
	}

	private function _configExports(){
		$out = [];
		$out[Crunchbutton_Support::CONFIG_KEY_CS_CALL_DRIVER] = Crunchbutton_Support::callDriverOnCS();
		$phone = Crunchbutton_Support::callDefaultPhoneNumber();
		$out[Crunchbutton_Support::CONFIG_KEY_CS_CALL_DEFAULT_PHONE] = $phone ? $phone : '';

		echo json_encode($out);exit;
	}
}