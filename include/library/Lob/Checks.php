<?php

namespace Lob;

use Lob\Resource;

class Checks extends Resource {
	public function __construct($lob) {
		$this->_lob = $lob;
		$this->_resourceName = 'checks';
	}
}