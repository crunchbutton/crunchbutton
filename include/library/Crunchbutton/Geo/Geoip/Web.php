<?php

class Crunchbutton_Geo_Geoip_Web extends Crunchbutton_Geo_Geoip {
	private $_apiKey;
	private $_ip;

	public function __construct($params=array()) {
		$this->loadParams($params);
	}

	public function lookupIp($ip=null) {
		if (!$ip) $ip = $this->getIp();

		$ret = array();
		//b
		$res = file_get_contents('http://geoip3.maxmind.com/f?l='.$this->getApiKey().'&i='.$ip);
		$res = explode(',',$res);

		$ret['countryCode'] = $res[0];		
		$ret['city'] = $res[2];

		$ret['latitude'] = $res[4];
		$ret['longitude'] = $res[5];
		$ret['region'] = $res[1];
		$ret['postalCode'] = $res[3];
		
		$ret['areaCode'] = $res[7];
		$ret['dmaCode'] = $res[6];
		
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
}
