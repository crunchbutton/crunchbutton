<?php

class Crunchbutton_Order_Logistics_Destination extends Cana_Model {

    const TYPE_DRIVER = 1;
    const TYPE_RESTAURANT = 2;
    const TYPE_CUSTOMER = 3;

    public $geo; // Crunchbutton_Order_Location
    public $parkingTime; // minutes
    public $serviceTime; // minutes
    public $type;  // Driver cur location, restaurant, customer
    public $saveTime; // Actual server time
    public $timeWindowBegin; // minutes from now
    public $timeWindowEnd; // minutes from now
    public $address;
    public $id;
    public $id_unique;
    public $cluster;

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