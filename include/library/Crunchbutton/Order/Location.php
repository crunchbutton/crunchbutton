<?php

class Crunchbutton_Order_Location {
	public $lat;
	public $lon;
	public $dt; // Optional

	public function __construct($lat, $lon) {
		$this->lat = $lat;
		$this->lon = $lon;
	}
}