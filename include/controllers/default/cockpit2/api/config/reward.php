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

		// save referral stuff
		$referral = $this->request()[ 'referral' ];
		foreach( $referral as $key => $val ){
			$key = str_replace( '_', '-', $key );
			$config = Crunchbutton_Config::getConfigByKey( $key );
			if( $config->key ){
				$config->set( intval( $val ) );
				$config->save();
			}
		}

		// save reward stuff
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

		// referral stuff

		$referral[ Crunchbutton_Referral::KEY_IS_REFERRAL_ENABLE ] = Crunchbutton_Referral::isReferralEnable();
		$referral[ Crunchbutton_Referral::KEY_ADD_CREDIT_INVITED ] = Crunchbutton_Referral::getAddCreditToInvited();
		$referral[ Crunchbutton_Referral::KEY_INVITES_LIMIT_PER_CODE ] = intval( Crunchbutton_Referral::getInvitesLimit() );
		$referral[ Crunchbutton_Referral::KEY_INVITER_CREDIT_VALUE ] = intval( Crunchbutton_Referral::getInviterCreditValue() );
		$referral[ Crunchbutton_Referral::KEY_INVITED_CREDIT_VALUE ] = intval( Crunchbutton_Referral::getInvitedCreditValue() );

		foreach( $referral as $key => $val ){
			$out[ 'referral' ][ str_replace( '-', '_', $key ) ] = $val;
		}



		echo json_encode( $out );
		exit;
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}