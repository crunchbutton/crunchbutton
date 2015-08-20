<?php

namespace Lob;

use Lob\Resource;

class BankAccounts extends Resource {
	public function __construct($lob) {
		$this->_lob = $lob;
		$this->_resourceName = 'bank_accounts';
	}

	public function verify( $id ) {
		$resource = $this->_resourceName . '/' . $id  . '/verify';
		$params = [ 'amounts' => [ 1, 2 ] ];
		return $this->_lob->request( $resource, $params, 'POST');
	}
}