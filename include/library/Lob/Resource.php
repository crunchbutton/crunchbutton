<?php

namespace Lob;

class Resource {
	public function __construct($params = null) {
		if (is_array($params)) {
			$this->create($params);
		} elseif ($params) {
			$this->load($params);
		}
	}

	public function create($params) {
		$r = $this->lob()->request($this->resourceName(), $params, 'POST');
		$this->_properties = (array)$r;
		return $this;
	}
	
	public function load($id) {
		$this->lob()->request($this->resourceName(), ['id' => $id], 'GET');
		$this->_properties = (array)$r;
		return $this;
	}
	
	public function resourceName() {
		return $this->_resourceName;
	}
	
	public function lob() {
		return $this->_lob;
	}
	
	public function &__get($name) {
		if (isset($name{0}) && $name{0} == '_') {
			return $this->{$name};
		} else {
			return $this->_properties[$name];
		}
	}

	public function __set($name, $value) {
		if ($name{0} == '_') {
			return $this->{$name} = $value;
		} else {
			return $this->_properties[$name] = $value;
		}
	}

	public function __isset($name) {
		return $name{0} == '_' ? isset($this->{$name}) : isset($this->_properties[$name]);
	}
	
	public function properties() {
		return $this->_properties;
	}
}