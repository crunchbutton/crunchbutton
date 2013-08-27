<?php

class Crunchbutton_Referral extends Cana_Table{

	const KEY_INVITER_CREDIT_VALUE = 'referral-inviter-credit-value';
	const DEFAULT_INVITER_CREDIT_VALUE = 1;

	const KEY_INVITED_CREDIT_VALUE = 'referral-invited-credit-value';
	const DEFAULT_INVITED_CREDIT_VALUE = 1;

	const KEY_ADD_CREDIT_INVITED = 'referral-add_credit-to-invited';
	const DEFAULT_ADD_CREDIT_INVITED = false;

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('referral')
			->idVar('id_referral')
			->load($id);
	}

	public function checkCookie(){
		if ( isset( $_COOKIE['referral'] ) && $_COOKIE['referral'] != '' ) {
			return $_COOKIE['referral'];
		} else {
			return false;
		}
	}

	public function removeCookie(){
		setcookie( 'referral', '', time() -3600, '/' );
	}

	public function validCode( $code ){
		$user = Crunchbutton_User::byInviteCode( $code );
		if( $user->id_user ){
			return $user;
		} else {
			return false;
		}
	}

	public function addCreditToInviter(){
		if( $this->new_user == 1 ){
			$credit = new Crunchbutton_Credit();
			$credit->id_user = $this->id_user_inviter;
			$credit->id_referral = $this->id_referral;
			$credit->type = Crunchbutton_Credit::TYPE_CREDIT;
			$credit->date = date('Y-m-d H:i:s');
			$credit->value = 1;
			$credit->paid_by = 'crunchbutton';
			$credit->note = 'Referral: ' . $this->id_referral;
			$credit->save();

			$this->addCreditToInvited();

		}
	}

	public function addCreditToInvited(){
		if( $this->new_user == 1 ){
			$credit = new Crunchbutton_Credit();
			$credit->id_user = $this->id_user_invited;
			$credit->id_referral = $this->id_referral;
			$credit->type = Crunchbutton_Credit::TYPE_CREDIT;
			$credit->date = date('Y-m-d H:i:s');
			$credit->value = 1;
			$credit->paid_by = 'crunchbutton';
			$credit->note = 'Referral: ' . $this->id_referral;
			$credit->save();
		}
	}


	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime($this->date, new DateTimeZone(c::config()->timezone));
			$this->_date->setTimezone(new DateTimeZone( c::config()->timezone ));
		}
		return $this->_date;
	}

	public function getInviterCreditValue(){
		$config = Crunchbutton_Config::q( 'SELECT * FROM config WHERE `key`="'. self::KEY_INVITER_CREDIT_VALUE . '" LIMIT 0,1' );
		if( $config->id_config && $config->val() ){
			return $config->val();
		} 
		return self::DEFAULT_INVITER_CREDIT_VALUE;
	}

	public function getInvitedCreditValue(){
		$config = Crunchbutton_Config::q( 'SELECT * FROM config WHERE `key`="'. self::KEY_INVITED_CREDIT_VALUE . '" LIMIT 0,1' );
		if( $config->id_config && $config->val() ){
			return $config->val();
		} 
		return self::DEFAULT_INVITED_CREDIT_VALUE;
	}

	public function getAddCreditToInvited(){
		$config = Crunchbutton_Config::q( 'SELECT * FROM config WHERE `key`="'. self::KEY_ADD_CREDIT_INVITED . '" LIMIT 0,1' );
		if( $config->id_config && $config->val() && $config->val() > 0 ){
			return true;
		} 
		return self::DEFAULT_ADD_CREDIT_INVITED;
	}

}