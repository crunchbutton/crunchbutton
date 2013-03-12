<?php

class Crunchbutton_Geo_VirtualEarth extends Caffeine_Geo_Geoip {
	private $_apiKey;
	private $_latitude;
	private $_longitude;

	public function __construct($params=array()) {
		$this->loadParams($params);
	}

	public function lookup($latitude, $longitude) {
		$res = file_get_contents('http://dev.virtualearth.net/REST/v1/Locations/'.$latitude.','.$longitude.'?key='.$this->getApiKey());
		$res = json_decode($res);
		$res = $res->resourceSets[0]->resources[0]->address;

		$ret['countryCode'] = $res->countryRegion == 'United States' ? 'US' : '';
		$ret['countryCode3'] = $res->countryRegion == 'United States' ? 'USA' : '';
		$ret['countryName'] = $res->countryRegion;
		
		$ret['city'] = $res->locality;
		$ret['latitude'] = $latitude;
		$ret['longitude'] = $longitude;
		$ret['region'] = $res->adminDistrict;
		$ret['postalCode'] = $res->postalCode;
		$ret['address'] = $res->addressLine;

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
