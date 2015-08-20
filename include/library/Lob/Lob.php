<?php

namespace Lob;

class Lob {
	public function __construct($key = null, $account = null) {
		$this->_key = $key;
		$this->_defaultAccount = $account;
	}

	public function checks() {
		if (!$this->_checks) {
			$this->_checks = new Checks($this);
		}
		return $this->_checks;
	}

	public function addresses() {
		if (!$this->_addresses) {
			$this->_addresses = new Addresses($this);
		}
		return $this->_addresses;
	}

	public function bankAccounts(){
		if (!$this->_bank_accounts) {
			$this->_bank_accounts = new BankAccounts($this);
		}
		return $this->_bank_accounts;
	}

	public function key() {
		return $this->_key;
	}

	public function defaultAccount() {
		return $this->_defaultAccount;
	}

	public function request($resource, $params, $method = 'GET') {
		$headers = [
			'Accept' => 'application/json; charset=utf-8',
			'User-Agent' => 'lob-crunchbutton-php-wrapper-v1',
		];
		$request = new \Cana_Curl('https://api.lob.com/v1/'.$resource, $params, strtolower($method), null, $headers, null, ['user' => $this->key(),'pass' => '']);

		$out = json_decode($request->output);
		if ($out->errors) {
			throw new \Cana_Exception( $out->errors[0]->message );
		}
		return $out;
	}
}