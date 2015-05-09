<?php

class Cana_Cache_Redis extends Cana_Model {
	public function __construct($params) {
		if (isset($params->server)) {
			$this->server = $params->server;
		}
		if (isset($params->expire)) {
			$this->expire = $params->expire;
		}
		
		$this->redis = new Predis\Client(array(
			'host' => parse_url($this->server, PHP_URL_HOST),
			'port' => parse_url($this->server, PHP_URL_PORT),
			'password' => parse_url($this->server, PHP_URL_PASS),
		));
	}
	
	public function cached($fileName, $expire = null) {
		$v = $this->read($fileName);
		return $v ? $v : false;
	}
	
	public function read($fileName) {
		die($this->redis->get($filename));
		//return unserialize();
	}

	public function write($fileName, $file) {
		return $this->redis->set($filename, serialize($file));
	}
	
	public function mtime($fileName) {
		return time();
	}
}