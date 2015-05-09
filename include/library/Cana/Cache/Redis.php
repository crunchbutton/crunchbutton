<?php

class Cana_Cache_Redis extends Cana_Model {
	public function __construct($params) {
		if (isset($params->url)) {
			$this->url = $params->url;
		}
		if (isset($params->expire)) {
			$this->expire = $params->expire;
		}

		$this->_redis = new Predis\Client(array(
			'host' => parse_url($this->url, PHP_URL_HOST),
			'port' => parse_url($this->url, PHP_URL_PORT),
			'password' => parse_url($this->url, PHP_URL_PASS),
		));
	}
	
	public function cached($key, $expire = null) {
		$v = $this->read($key);
		return $v ? $v : false;
	}
	
	public function read($key) {
		return $this->redis()->get($key);
	}

	public function write($key, $file) {
		$this->redis()->set($key, $file);
	}
	
	public function mtime($key) {
		return time();
	}
	
	public function redis() {
		return $this->_redis;
	}
}