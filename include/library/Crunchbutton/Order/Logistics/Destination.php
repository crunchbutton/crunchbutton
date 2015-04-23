<?php

class Crunchbutton_Order_Logistics_Destination extends Cana_Model {
	public function __construct($params = []) {
		foreach ($params as $key => $param) {
			$this->{$key} = $param;
		}

		/*
		// calculate the distance from the driver
		if ($this->from) {
			$distance = $this->driver->distance($this->address);
			$this->distance = $distance->distance;
			$this->time = $distance->time;
		}
		*/
	}
}