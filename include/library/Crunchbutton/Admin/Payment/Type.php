<?php

class Crunchbutton_Admin_Payment_Type extends Cana_Table {

	const PAYMENT_METHOD_DEPOSIT = 'deposit';
	const PAYMENT_TYPE_HOURS = 'hours';
	const PAYMENT_TYPE_ORDERS = 'orders';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('admin_payment_type')
			->idVar('id_admin_payment_type')
			->load($id);
	}

	public function social_security_number( $id_admin ){
		return Crunchbutton_Admin_Info::getSSN( $id_admin );
	}

	public function save_social_security_number( $id_admin, $ssn ){
		return Crunchbutton_Admin_Info::storeSSN( $id_admin, $ssn );
	}

	// alias
	public function ssn( $id_admin ){
		return Crunchbutton_Admin_Payment_Type::social_security_number( $id_admin );
	}

	public function save_ssn( $id_admin, $ssn ){
		return Crunchbutton_Admin_Payment_Type::save_social_security_number( $id_admin, $ssn );
	}

	function byAdmin( $id_admin ){
		if( $id_admin ){
			$payment = Crunchbutton_Admin_Payment_Type::q( 'SELECT * FROM admin_payment_type WHERE id_admin = ' . $id_admin . ' ORDER BY id_admin_payment_type DESC LIMIT 1' );
			if( $payment->id_admin_payment_type ){
				return Crunchbutton_Admin_Payment_Type::o( $payment->id_admin_payment_type );
			}
		}
		$payment = new Crunchbutton_Admin_Payment_Type();
		$payment->id_admin = $id_admin;
		$payment->save();
		return $payment;
	}

	function claimBankAccount( $bank_account ){
		$env = c::getEnv();
		$headers = [ "Accept: application/vnd.api+json;revision=1.1" ];
		$url = 'https://api.balancedpayments.com/bank_accounts/' . $bank_account;
		$auth = c::config()->balanced->{$env}->secret;
		$request = new \Cana_Curl($url, null, 'get', null, $headers, null, [ 'user' => $auth, 'pass' => '' ] );
		Log::debug( [ 'request' => $request, 'type' => 'claim-account' ] );
	}

	public function using_pex_date(){
		if (!isset($this->_using_pex_date)) {
			$this->_using_pex_date = new DateTime($this->using_pex_date, new DateTimeZone(c::config()->timezone));
		}
		return $this->_using_pex_date;
	}

}