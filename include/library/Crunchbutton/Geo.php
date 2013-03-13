<?php

class Crunchbutton_Geo {
	private $_ipAdapter;
	private $_ipAdapterName = 'Geoip_Binary';
	private $_city;
	private $_postalCode;
	private $_areaCode;
	private $_countryCode;
	private $_countryName;
	private $_countryCode3;
	private $_region;
	private $_ip;
	private $_dmaCode;
	private $_hostName;


	public function __construct($params=array()) {

		$this->loadParams($params);
	}

	public function loadParams($params) {
		if (isset($params['adapter'])) {
			$this->setIpAdapterName($params['adapter']);
		}
		$adapterName = 'Crunchbutton_Geo_'.$this->getIpAdapterName();
		$this->setIpAdapter(new $adapterName($params));

		if (isset($params['adapter'])) {
			$this->setIpAdapterName($params['adapter']);
		}
	}

	public function populateHostName() {
		$host = $this->getIp();

		for ($x=0;$x<=10;$x++) {
			$host = gethostbyaddr($this->getIp());
			if ($host != $this->getIp()) break;
		}
		$this->setHostName($host);
	}

	public function populateByIp() {
		$loc = $this->getIpAdapter()->lookupIp($this->getIp());
		if (count($loc)) {
			$this->populateByResult($loc);
		}
//		$this->populateHostName();

		return $this;
	}
	
	public function populateByLatLong() {
		$loc = $this->getIpAdapter()->lookup($this->getLatitude(),$this->getLongitude());
		if (count($loc)) {
			$this->populateByResult($loc);
		}
		//$this->populateHostName();

		return $this;
	}

	public function populateByResult($loc) {
		if (count($loc)) {
			$this->setCity($loc['city'])
				->setPostalCode($loc['postalCode'])
				->setAreaCode($loc['areaCode'])
				->setCountryCode($loc['countryCode'])
				->setCountryCode3($loc['countryCode3'])
				->setRegion($loc['region'])
				->setDmaCode($loc['dmaCode'])
				->setCountryName($loc['countryName']);
			if ($loc['latitude']) {
				$this->setLatitude($loc['latitude'])
					->setLongitude($loc['longitude']);
			}
		}
	}

	public function getIpAdapterName() {
		return $this->_ipAdapterName;
	}
	
	public function setIpAdapterName($name) {
		$this->_ipAdapterName = $name;
		return $this;
	}

	public function getIpAdapter() {
		return $this->_ipAdapter;
	}

	public function setIpAdapter($adapter) {
		$this->_ipAdapter = $adapter;
		return $this;
	}

	public function getIp() {
		return $this->_ip;
	}

	public function setIp($ip) {
		$this->_ip = $ip;
		return $this;
	}

	public function getCity() {
		return $this->_city;
	}

	public function setCity($city) {
		$this->_city = $city;
		return $this;
	}

	public function getCountryName() {
		return $this->_countryName;
	}

	public function setCountryName($name) {
		$this->_countryName = $name;
		return $this;
	}

	public function getCountryCode() {
		return $this->_countryCode;
	}

	public function setCountryCode($code) {
		$this->_countryCode = $code;
		return $this;
	}

	public function getCountryCode3() {
		return $this->_countryCode3;
	}

	public function setCountryCode3($code) {
		$this->_countryCode3 = $code;
		return $this;
	}

	public function getPostalCode() {
		return $this->_postalCode;
	}

	public function setPostalCode($code) {
		$this->_postalCode = $code;
		return $this;
	}

	public function getAreaCode() {
		return $this->_areaCode;
	}

	public function setAreaCode($code) {
		$this->_areaCode = $code;
		return $this;
	}

	public function getRegion() {
		return $this->_region;
	}

	public function setRegion($region) {
		$this->_region = $region;
		return $this;
	}

	public function getDmaCode() {
		return $this->_dmaCode;
	}

	public function setDmaCode($code) {
		$this->_dmaCode = $code;
		return $this;
	}

	public function getHostName() {
		return $this->_hostName;
	}

	public function setHostName($host) {
		$this->_hostName = $host;
		return $this;
	}
	
	public function setLatitude($latitude) {
		$this->_latitude = $latitude;
		return $this;
	}
	
	public function getLatitude() {
		return $this->_latitude;
	}
	
	public function setLongitude($longitude) {
		$this->_longitude = $longitude;
		return $this;
	}
	
	public function getLongitude() {
		return $this->_longitude;
	}
}

