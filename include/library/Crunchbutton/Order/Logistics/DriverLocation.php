<?php

class Crunchbutton_Order_Logistics_DriverLocation {
    const DEFAULT_TIME = 1200; // 20 minutes * 60 seconds
    const DEFAULT_HALF_TIME = 600; // 10 minutes * 60 seconds
    const DEFAULT_FACTOR = 0.9; // If food hasn't been delivered in 20 minutes, assume driver is 90% of way to destination.
    const MAX_DRIVER_NO_LOC = 3600; // seconds = 60 minutes * 60 seconds
    const MAX_DRIVER_VELOCITY = 0.000312928; // deg / sec, using 100 km /deg, and 70 mph = 0.0312928 km/s
    const DRIVER_LOCATION_TIME_WINDOW = 300; // 5 min * 60 seconds

    const RESTAURANT_LOC = 1;
    const CUSTOMER_LOC = 2;
    const RESTAURANT_CLUSTER_LOC = 3;
    const COMMUNITY_CENTER_LOC = 4;

	public $communityCenter;
	public $lat;
	public $lon;

    public $numPickedUpOrders;
    public $numAcceptedOrders;
    public $numDeliveredOrders;

    public $earliestPickedUpTS;
    public $earliestAcceptedTS;

    public $beginTS;
    public $beginLat;
    public $beginLon;
    public $beginType;


    public $endLat;
    public $endLon;
    public $endType;

    public $latestActionTS;

    // cluster represents where the driver would hang out if he has no orders to deliver
    //  Most likely near restaurants.
    public $clusterLat;
    public $clusterLon;


    public $hasGeo;

	public function __construct($communityCenter) {
		$this->communityCenter = $communityCenter;
		$this->lat = $communityCenter->lat;
		$this->lon = $communityCenter->lon;

        $this->numPickedUpOrders = 0;
        $this->numAcceptedOrders = 0;
        $this->numDeliveredOrders = 0;

        $this->beginLat = $this->lat;
        $this->beginLon = $this->lon;
        $this->beginType = self::COMMUNITY_CENTER_LOC;

        $this->endType = 0;

        $this->latestActionTS = null;
        $this->hasGeo = false;
	}

    public function getTravelTime($beginType, $endType) {
        if ($beginType == self::CUSTOMER_LOC && $endType == self::CUSTOMER_LOC) {
            return self::DEFAULT_HALF_TIME;
        } else if ($beginType == self::CUSTOMER_LOC && $endType == self::RESTAURANT_LOC) {
            return self::DEFAULT_TIME;
        } else if ($beginType == self::RESTAURANT_LOC && $endType == self::CUSTOMER_LOC) {
            return self::DEFAULT_TIME;
        } else if ($beginType == self::COMMUNITY_CENTER_LOC && $endType == self::RESTAURANT_LOC) {
            return -100000;
        } else if ($beginType == self::CUSTOMER_LOC && $endType == self::COMMUNITY_CENTER_LOC) {
            return self::DEFAULT_HALF_TIME;
        } else if ($beginType == self::COMMUNITY_CENTER_LOC && $endType == self::RESTAURANT_CLUSTER_LOC) {
            return -100000;
        } else if ($beginType == self::RESTAURANT_LOC && $endType == self::RESTAURANT_LOC) {
            return self::DEFAULT_HALF_TIME;
        } else if ($beginType == self::RESTAURANT_LOC && $endType == self::RESTAURANT_CLUSTER_LOC) {
            return self::DEFAULT_HALF_TIME;
        } else if ($beginType == self::CUSTOMER_LOC && $endType == self::RESTAURANT_CLUSTER_LOC) {
            return self::DEFAULT_TIME;
        } else {
            return self::DEFAULT_HALF_TIME;
        }

    }

    public function calcCurLocation($curTS) {
        $travelTime = $this->getTravelTime($this->beginType, $this->endType);

        $beginTS = $this->beginTS;
        if (is_null($beginTS)) {
            $beginTS = $curTS;
        }

        if ($travelTime == 0) {
            $this->lat = $this->endLat;
            $this->lon = $this->endLon;
        } else {
            if ($beginTS + $travelTime > $curTS) {
                $endWeight = $curTS - $beginTS;
                $beginWeight = $beginTS + $travelTime - $curTS;
                $this->lat = $this->wavg($this->beginLat, $this->endLat, $beginWeight, $endWeight);
                $this->lon = $this->wavg($this->beginLon, $this->endLon, $beginWeight, $endWeight);
            } else {
                $endWeight = self::DEFAULT_FACTOR;
                $beginWeight = 1 - $endWeight;
                $this->lat = $this->wavg($this->beginLat, $this->endLat, $beginWeight, $endWeight);
                $this->lon = $this->wavg($this->beginLon, $this->endLon, $beginWeight, $endWeight);
            }
        }
    }


    public function fitLatLon($locs, $n, $newTime){
        // Combine some of the regression calculations to improve efficiency slightly
        // No error checking on the input arrays for efficiency reasons.  This should be done outside.
        // $locs is an array with $date, $lat, $lon

        // Adjust everything so that $newTime = 0
        // Things get weird if you use the large timestamp numbers, so it's better to subtract a large offset.

        $timeSum = 0; // X
        $latSum = 0; // Y
        $lonSum = 0; // Y
        $sumTime2 = 0;
        $sumTimeLat = 0;
        $sumTimeLon = 0;

        for ($i=0; $i<$n; $i++) {
            // subtract off $newTime
            $time = $locs->get($i)->ts - $newTime;
            $lat = $locs->get($i)->lat;
            $lon = $locs->get($i)->lon;
            $timeSum += $time;
            $latSum += $lat;
            $lonSum +=$lon;
            $sumTime2 += pow($time, 2);
            $sumTimeLat += $time * $lat;
            $sumTimeLon += $time * $lon;
        }
        $timeMean = $timeSum / $n;
        $latMean = $latSum / $n;
        $lonMean = $lonSum / $n;
        $ss_xx = ($sumTime2) - ($n * $timeMean * $timeMean);
        $ss_timeLat = ($sumTimeLat) - ($n * $timeMean * $latMean);
        $ss_timeLon = ($sumTimeLon) - ($n * $timeMean * $lonMean);
        if ($ss_xx == 0) {
            return null;
        } else {
            $slopeLat = $ss_timeLat / $ss_xx;
            $slopeLon = $ss_timeLon / $ss_xx;
        }

        if (abs($slopeLat) > self::MAX_DRIVER_VELOCITY) {
            if ($slopeLat >= 0) {
                $slopeLat = self::MAX_DRIVER_VELOCITY;
            } else{
                $slopeLat = -1 * self::MAX_DRIVER_VELOCITY;
            }
            // Original used average, but it washes out the effect too much
//            $interceptLat = $latMean - ($slopeLat * $timeMean);
//            // Code to adjust slope here, then
//            $interceptLat2 = $latMean - ($slopeLat * $timeMean);
//            $interceptLat = ($interceptLat + $interceptLat2) / 2.0;
        }
        $interceptLat = $latMean - ($slopeLat * $timeMean);

        if (abs($slopeLon) > self::MAX_DRIVER_VELOCITY) {
            if ($slopeLon >= 0) {
                $slopeLon = self::MAX_DRIVER_VELOCITY;
            } else {
                $slopeLon = -1 * self::MAX_DRIVER_VELOCITY;
            }
        }
        $interceptLon = $lonMean - ($slopeLon * $timeMean);
        // Remember that $newTime was adjusted to 0.
        $lat = $interceptLat;
        $lon = $interceptLon;
        return new Crunchbutton_Order_Location($lat, $lon);

    }

    public function avgLatLon($locs, $n){

        if ($n > 0) {
            $latSum = 0; // Y
            $lonSum = 0; // Y

            for ($i = 0; $i < $n; $i++) {
                $lat = $locs->get($i)->lat;
                $lon = $locs->get($i)->lon;
                $latSum += $lat;
                $lonSum += $lon;
            }
            $latMean = $latSum / $n;
            $lonMean = $lonSum / $n;
            return new Crunchbutton_Order_Location($latMean, $lonMean);
        } else{
            return null;
        }
    }

    public function determineDriverGeo($driver, $serverDT) {
        $driverGeo = $this->calcDriverGeoFromLocations($driver, $serverDT);
        if (!is_null($driverGeo)) {
            $tmpLat = $driverGeo->lat;
            $tmpLon = $driverGeo->lon;
            $this->lat = $driverGeo->lat;
            $this->lon = $driverGeo->lon;
        }
    }


    public function calcDriverGeoFromLocations($driver, $serverDT)
    {

        $result = null;
        $windowDT = clone $serverDT;
        $windowDT->modify('- ' . self::MAX_DRIVER_NO_LOC . ' seconds');
        $location = $driver->locationWithMaxTime($windowDT);
        if (!is_null($location)){
            $driverWindowDT = new DateTime($location->date, new DateTimeZone(c::config()->timezone));
            $driverWindowDT->modify('- ' . self::DRIVER_LOCATION_TIME_WINDOW . ' seconds');
            $locations = $driver->locationsWithMaxTime($driverWindowDT);
            $numLocations = $locations->count();
            if ($numLocations >= 5){
                if ($numLocations > 15) {
                    $numLocations = 15;
                }
                $result = $this->fitLatLon($locations, $numLocations, $serverDT->getTimestamp());
            } else{
                $result = $this->avgLatLon($locations, $numLocations);
            }

        }
        return $result;

    }


    public function addPickedUpOrder($actionTimestamp, $curTimestamp, $restaurantGeo, $orderLat, $orderLon){

        if (!is_null($restaurantGeo) && !is_null($restaurantGeo->lat) && !is_null($restaurantGeo->lon) &&
            !is_null($orderLat) && !is_null($orderLon)) {

            $this->numPickedUpOrders++;
            $isChanged = false;

            if (is_null($this->latestActionTS) || $actionTimestamp > $this->latestActionTS) {
                $this->latestActionTS = $actionTimestamp;
                $this->beginTS = $actionTimestamp;
                $this->beginLat = $restaurantGeo->lat;
                $this->beginLon = $restaurantGeo->lon;
                $this->beginType = self::RESTAURANT_LOC;
                $isChanged = true;
            }
            if ($this->numPickedUpOrders == 1 || $actionTimestamp < $this->earliestPickedUpTS) {
                $this->earliestPickedUpTS = $actionTimestamp;
                $this->endLat = $orderLat;
                $this->endLon = $orderLon;
                $this->endType = self::CUSTOMER_LOC;
                $isChanged = true;
            }

            if ($isChanged) {
                $this->calcCurLocation($curTimestamp);
            }

        }

    }

    public function addAcceptedOrder($actionTimestamp, $curTimestamp, $restaurantGeo){
        if (!is_null($restaurantGeo) && !is_null($restaurantGeo->lat) && !is_null($restaurantGeo->lon)) {
            $this->numAcceptedOrders++;

            $isChanged = false;

            if ($this->numPickedUpOrders == 0 && $this->numDeliveredOrders == 0) {
                if ($this->numAcceptedOrders == 1 || $actionTimestamp < $this->earliestAcceptedTS) {
                    $this->earliestAcceptedTS = $actionTimestamp;
                    $this->beginTS = $actionTimestamp;
                    $this->beginLat = $this->communityCenter->lat;
                    $this->beginLon = $this->communityCenter->lon;
                    $this->beginType = self::COMMUNITY_CENTER_LOC;
                    $isChanged = true;
                }
            }
            if ($this->numPickedUpOrders == 0) {
                if ($this->numAcceptedOrders == 1 || $actionTimestamp <= $this->earliestAcceptedTS) {
                    $this->earliestAcceptedTS = $actionTimestamp;
                    $this->endLat = $restaurantGeo->lat;
                    $this->endLon = $restaurantGeo->lon;
                    $this->endType = self::RESTAURANT_LOC;
                    $isChanged = true;
                }
            }

            if ($isChanged) {
                $this->calcCurLocation($curTimestamp);
            }
        }

    }

    public function addDeliveredOrder($actionTimestamp, $curTimestamp, $orderLat, $orderLon, $restaurantGeo){
        // Use restaurant info as a cluster proxy

        if (!is_null($restaurantGeo) && !is_null($restaurantGeo->lat) && !is_null($restaurantGeo->lon) &&
            !is_null($orderLat) && !is_null($orderLon)) {

            $isChanged = false;
            if (($actionTimestamp + self::DEFAULT_TIME) > $curTimestamp) {

                $this->numDeliveredOrders++;

                if (is_null($this->latestActionTS) || $actionTimestamp > $this->latestActionTS) {
                    $this->latestActionTS = $actionTimestamp;
                    $this->beginTS = $actionTimestamp;
                    $this->beginLat = $orderLat;
                    $this->beginLon = $orderLon;
                    $this->beginType = self::CUSTOMER_LOC;
                    $isChanged = true;
                }
            }

            if (is_null($this->clusterLat) && is_null($this->clusterLon) && $this->numAcceptedOrders == 0 &&
                $this->numPickedUpOrders == 0) {

                $this->clusterLat = $restaurantGeo->lat;
                $this->clusterLon = $restaurantGeo->lon;

                $this->endLat = $this->clusterLat;
                $this->endLon = $this->clusterLon;
                $this->endType = self::RESTAURANT_CLUSTER_LOC;
                $isChanged = true;
            }

            if ($isChanged) {
                $this->calcCurLocation($curTimestamp);
            }
        }

    }

    public function wavg($x1, $x2, $w1, $w2) {
        $sumWeight = $w1+$w2;
        if ($sumWeight != 0) {
            return (($w1 * $x1) + ($w2 * $x2)) / $sumWeight;
        } else{
            return 0.0;
        }
    }


}