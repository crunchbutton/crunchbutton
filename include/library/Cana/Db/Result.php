<?php
/**
 * Db result set
 *
 * @date		2012.03.27
 * @author		Devin Smith <devin@cana.la>
 *
 */

class Cana_Db_Result extends Cana_Iterator {

	private $_db;
	private $_res;

	public function __construct($res, $db) {
		$this->_res = $res;
		$this->_db = $db;
	}
	
	public function db() {
		return $this->_db;
	}

	public function res() {
		return $this->_res;
	}
	
	public function __call($name, $arguments) {
		if (method_exists($this->res(),$name)) {
			return (new ReflectionMethod($this->res(), $name))->invokeArgs($this->res(), $arguments);
		} else {
			return (new ReflectionMethod(parent, $name))->invokeArgs(parent, $arguments);
		}
	}

	public function &__get($name) {
		echo $name;
		if (property_exists($this->res(),$name)) {
			return $this->res()->{$name};
		} else {
			return parent::__get($name);
		}
	}
}