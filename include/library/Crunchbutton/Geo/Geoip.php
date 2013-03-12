<?php


class Crunchbutton_Geo_Geoip {
	private $_ip;

	public function setIp($ip) {
		$this->_id = $ip;
		return $this;
	}

	public function getIp() {
		return $this->_ip;
	}
}
