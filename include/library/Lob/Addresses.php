<?php

namespace Lob;

use Lob\Resource;

class Addresses extends Resource {
	public function __construct($lob) {
		$this->_lob = $lob;
		$this->_resourceName = 'addresses';
	}
}