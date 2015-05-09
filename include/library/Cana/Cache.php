<?php

class Cana_Cache extends Cana_Model {
	public $suffix = '.cache';

	public function __construct($params) {
		if ($params->adapter == 'redis') {
			$this->adapter(new Cana_Cache_Redis($params));
		} else {
			$this->adapter(new Cana_Cache_File($params));
		}
	}
	
	public function __call($name, $arguments) {
		print_r($arguments);
		die($name);
		return (new ReflectionMethod($this->adapter(), $name))->invokeArgs($this->adapter(), $arguments);
	}
	
	public function adapter($adapter = null) {
		if (!is_null($adapter)) {
			$this->_adapter = $adapter;
		}
		return $this->_adapter;
	}

}