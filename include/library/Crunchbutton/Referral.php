<?php

class Crunchbutton_Referral extends Cana_Table{

	const KEY_INVITER_CREDIT_VALUE = 'referral-inviter-credit-value';
	const DEFAULT_INVITER_CREDIT_VALUE = 1;

	const KEY_INVITED_CREDIT_VALUE = 'referral-invited-credit-value';
	const DEFAULT_INVITED_CREDIT_VALUE = 1;

	const KEY_ADD_CREDIT_INVITED = 'referral-add-credit-to-invited';
	const DEFAULT_ADD_CREDIT_INVITED = false;

	const KEY_INVITES_LIMIT_PER_CODE = 'referral-invites-limit-per-code';
	const DEFAULT_INVITES_LIMIT_PER_CODE = 20;

	const KEY_IS_REFERRAL_ENABLE = 'referral-is-enable';
	const DEFAULT_IS_REFERRAL_ENABLE = false;

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

		$total_invites = $this->getInvitesPerCode( $this->invite_code );
		$limit_invites = $this->getInvitesLimit();

		if( intval( $total_invites ) >= intval( $limit_invites ) ){
			return;
		}

		if( $this->new_user == 1 ){
			$credit = new Crunchbutton_Credit();
			$credit->id_user = $this->id_user_inviter;
			$credit->id_referral = $this->id_referral;
			$credit->type = Crunchbutton_Credit::TYPE_CREDIT;
			$credit->credit_type = Crunchbutton_Credit::CREDIT_TYPE_CASH;
			$credit->date = date('Y-m-d H:i:s');
			$credit->value = $this->getInviterCreditValue();
			$credit->paid_by = 'crunchbutton';
			$credit->note = 'Referral inviter: ' . $this->id_referral;

			Log::debug([ 'referral_type' => 'inviter', 'id_user' => $credit->id_user,  'id_referral' => $credit->id_referral,  'type' => $credit->type,  'date' => $credit->date,  'value' => $credit->value,  'paid_by' => $credit->paid_by,  'note' => $credit->note, 'type' => 'referral' ]);

			$credit->save();
			if( $this->getAddCreditToInvited() ){
				$this->addCreditToInvited();
			}
		}
	}

	public function addCreditToInvited(){
		if( $this->new_user == 1 ){
			$credit = new Crunchbutton_Credit();
			$credit->id_user = $this->id_user_invited;
			$credit->id_referral = $this->id_referral;
			$credit->type = Crunchbutton_Credit::TYPE_CREDIT;
			$credit->credit_type = Crunchbutton_Credit::CREDIT_TYPE_CASH;
			$credit->date = date('Y-m-d H:i:s');
			$credit->value = $this->getInvitedCreditValue();
			$credit->paid_by = 'crunchbutton';
			$credit->note = 'Referral invited: ' . $this->id_referral;

			Log::debug([ 'referral_type' => 'invited', 'id_user' => $credit->id_user,  'id_referral' => $credit->id_referral,  'type' => $credit->type,  'date' => $credit->date,  'value' => $credit->value,  'paid_by' => $credit->paid_by,  'note' => $credit->note, 'type' => 'referral' ]);

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

	public function getInvitesPerCode( $code ){
		$invites = Crunchbutton_Referral::q( "SELECT * FROM referral WHERE invite_code = '{$code}' AND new_user = 1" );
		return $invites->count();
	}

	public function getInvitesLimit(){
		$config = Crunchbutton_Config::q( 'SELECT * FROM config WHERE `key`="'. self::KEY_INVITES_LIMIT_PER_CODE . '" LIMIT 0,1' );
		if( $config->id_config && $config->value ){
			return $config->value;
		}
		return self::DEFAULT_INVITES_LIMIT_PER_CODE;
	}

	public function getInviterCreditValue(){
		$config = Crunchbutton_Config::q( 'SELECT * FROM config WHERE `key`="'. self::KEY_INVITER_CREDIT_VALUE . '" LIMIT 0,1' );
		if( $config->id_config && $config->value ){
			return $config->value;
		}
		return self::DEFAULT_INVITER_CREDIT_VALUE;
	}

	public function getInvitedCreditValue(){
		$config = Crunchbutton_Config::q( 'SELECT * FROM config WHERE `key`="'. self::KEY_INVITED_CREDIT_VALUE . '" LIMIT 0,1' );
		if( $config->id_config && $config->value ){
			return $config->value;
		}
		return self::DEFAULT_INVITED_CREDIT_VALUE;
	}

	public function isReferralEnable(){
		$config = Crunchbutton_Config::q( 'SELECT * FROM config WHERE `key`="'. self::KEY_IS_REFERRAL_ENABLE . '" LIMIT 0,1' );
		if( $config->id_config && $config->value && intval( $config->value ) > 0 ){
			return true;
		}
		return self::DEFAULT_IS_REFERRAL_ENABLE;
	}

	public function getAddCreditToInvited(){
		$config = Crunchbutton_Config::q( 'SELECT * FROM config WHERE `key`="'. self::KEY_ADD_CREDIT_INVITED . '" LIMIT 0,1' );
		if( $config->id_config && $config->value && $config->value > 0 ){
			return true;
		}
		return self::DEFAULT_ADD_CREDIT_INVITED;
	}

}