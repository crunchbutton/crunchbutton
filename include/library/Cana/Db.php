<?php

/**
 * Database connectivity functions
 * 
 * @author		Devin Smith <devin@cana.la>
 * @date		2009.09.18
 * 
 */

class Cana_Db extends Cana_Model {
	private $_type = 'MySQL';
	private $_dbo;

	public function __construct($params) {
		if (is_array($params)) {
			$params = (object)$params;
		}
		if (isset($params->type)) {
			$this->_type = $params->type;
		}
		$type = 'Cana_Db_'.$this->_type.'_Db';
		$this->_dbo = new $type($params);
	}
	
	public function __call($name, $arguments = []) {
		return (new ReflectionMethod($this->dbo(), $name))->invokeArgs($this->dbo(), $arguments);
	}
	
	public function dbo() {
		return $this->_dbo;
	}
} 