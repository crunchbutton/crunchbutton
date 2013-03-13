<?php

class Crunchbutton_Geo_Ipinfo extends Caffeine_Model {
	private $_apiKey;
	private $_ip;

	public function __construct($params=array()) {
		$this->loadParams($params);
	}

	public function lookupIp($ip=null) {
		if (!$ip) $ip = $this->getIp();

		$ret = array();

		$res = file_get_contents('http://api.ipinfodb.com/v3/ip-city/?key='.$this->getApiKey().'&format=json&ip='.$ip);
		$location = json_decode($res);
		
		$ret['city'] = ucwords(strtolower($location->cityName));
		$ret['countryName'] = ucwords(strtolower($location->countryName));
		$ret['countryCode'] = $location->countryCode;
		$ret['latitude'] = $location->latitude;
		$ret['longitude'] = $location->longitude;
		$ret['region'] = ucwords(strtolower($location->regionName));
		$ret['postalCode'] = $location->zipCode;
		
		return $ret;
	}

	public function loadParams($params) {
		if (isset($params['apiKey'])) {
			$this->setApiKey($params['apiKey']);
		}
	}

	public function getApiKey() {
		return $this->_apiKey;
	}

	public function setApiKey($ip) {
		$this->_apiKey = $ip;
		return $ip;
	}

	public function setIp($ip) {
		$this->_id = $ip;
		return $this;
	}

	public function getIp() {
		return $this->_ip;
	}
}
