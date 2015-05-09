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
	
	public function read($key) {
		return unserialize($this->adapter()->read($key));
	}
	
	public function write($key, $value) {
		return $this->adapter()->write($key, serialize($value));
	}
	
	public function __call($name, $arguments) {
		return (new ReflectionMethod($this->adapter(), $name))->invokeArgs($this->adapter(), $arguments);
	}
	
	public function adapter($adapter = null) {
		if (!is_null($adapter)) {
			$this->_adapter = $adapter;
		}
		return $this->_adapter;
	}

}