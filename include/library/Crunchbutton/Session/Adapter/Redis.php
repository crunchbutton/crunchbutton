<?php

class Crunchbutton_Session_Adapter_Redis {
	public function __construct($params) {
		ini_set('session.save_handler', 'redis');
		//ini_set('session.save_path', 'tcp://'.parse_url($this->url, PHP_URL_HOST).':'.parse_url($this->url, PHP_URL_PORT).'?auth='.parse_url($this->url, PHP_URL_PASS));
		ini_set('session.save_path', 'tcp://redis-1.crunchbutton.arzynik.cont.tutum.io:6379?auth=4O3pdA0UEJhPXKEnUCvl9pJC1cYVsCrc');
	}

	public function generateAndSaveToken() {
		return false;
	}

	public function user() {
		return false;
	}

	public function save() {
		return false;
	}
}
