<?php

class Crunchbutton_Optimizer_Input extends Cana_Model {

    const TYPE_DRIVER = 1;
    const TYPE_RESTAURANT = 2;
    const TYPE_CUSTOMER = 3;

    const DISTANCE_LATLON = 1;
    const DISTANCE_XY = 2;

    public $numNodes;
    public $driverMph;
    public $penaltyCoefficient;
    public $customerDropoffTime;
    public $restaurantPickupTime;
    public $slackMaxTime;
    public $distanceType;
    public $horizon;
    public $maxRunTime;
    public $nodeTypes;
    public $firstCoords; // X, or lat
    public $secondCoords; // Y, or lon
    public $orderTimes;
    public $earlyWindows;
    public $midWindows;
    public $lateWindows;
    public $pickupIdxs;
    public $deliveryIdxs;
    public $clusters;
    public $restaurantParkingTimes;
    public $uuid;

	public function __construct($params = []) {
		foreach ($params as $key => $param) {
			$this->{$key} = $param;
		}

	}

    public function exports() {
        $d = [];
        $d["numNodes"] = $this->numNodes;
        $d["driverMph"] = $this->driverMph;
        $d["penaltyCoefficient"] = $this->penaltyCoefficient;
        $d["customerDropoffTime"] = $this->customerDropoffTime;
        $d["restaurantPickupTime"] = $this->restaurantPickupTime;
        $d["slackMaxTime"] = $this->slackMaxTime;
        $d["distanceType"] = $this->distanceType;
        $d["horizon"] = $this->horizon;
        $d["maxRunTime"] = $this->maxRunTime;
        $d["nodeTypes"] = $this->nodeTypes;
        $d["firstCoords"] = $this->firstCoords;
        $d["secondCoords"] = $this->secondCoords;
        $d["orderTimes"] = $this->orderTimes;
        $d["earlyWindows"] = $this->earlyWindows;
        $d["midWindows"] = $this->midWindows;
        $d["lateWindows"] = $this->lateWindows;
        $d["pickupIdxs"] = $this->pickupIdxs;
        $d["deliveryIdxs"] = $this->deliveryIdxs;
        $d["clusters"] = $this->clusters;
        $d["restaurantParkingTimes"] = $this->restaurantParkingTimes;
        $d["uuid"] = $this->uuid;
        return $d;
    }

}