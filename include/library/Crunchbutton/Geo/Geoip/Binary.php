<?php

require_once 'Net/GeoIP.php';

class Crunchbutton_Geo_Geoip_Binary extends Crunchbutton_Geo_Geoip {
	private $_fileName;
	private $_geoIp;

	public function __construct($params=array()) {
		$this->loadParams($params);
		$this->setGeoIp(Net_GeoIP::getInstance($this->getFileName()));
	}

	public function lookupIp($ip=null) {
		if (!$ip) $ip = $this->getIp();

		$ret = array();

		try {
		    $location = $this->getGeoIp()->lookupLocation($ip);
			$ret['city'] = $location->city;
			$ret['countryName'] = $location->countryName;
			$ret['countryCode'] = $location->countryCode;
			$ret['countryCode3'] = $location->countryCode3;
			$ret['latitude'] = $location->latitude;
			$ret['longitude'] = $location->longitude;
			$ret['region'] = $location->region;
			$ret['city'] = $location->city;
			$ret['areaCode'] = $location->areaCode;
			$ret['postalCode'] = $location->postalCode;
			$ret['dmaCode'] = $location->dmaCode;
		
		} catch (Exception $e) {
		    
		}
		return $ret;
	}

	public function loadParams($params) {
		if (isset($params['file'])) {
			$this->setFileName($params['file']);
		}
	}

	public function getGeoIp() {
		return $this->_geoIp;
	}

	public function setGeoIp($ip) {
		$this->_geoIp = $ip;
		return $ip;
	}

	public function getFileName() {
		return $this->_fileName;
	}

	public function setFileName($file) {
		$this->_fileName = $file;
		return $this;
	}
}
