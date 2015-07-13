<?php

class Crunchbutton_Order_Logistics_Destination extends Cana_Model {

    const TYPE_DRIVER = 1;
    const TYPE_RESTAURANT = 2;
    const TYPE_CUSTOMER = 3;

    public $objectId;
    public $type;  // Driver cur location, restaurant, customer
    public $geo; // Crunchbutton_Order_Location
    public $orderTime;
    public $earlyWindow;
    public $midWindow;
    public $lateWindow;
    public $restaurantParkingTime; // minutes
    public $cluster;
    public $isFake;


	public function __construct($params = []) {
		foreach ($params as $key => $param) {
			$this->{$key} = $param;
		}
	}
}