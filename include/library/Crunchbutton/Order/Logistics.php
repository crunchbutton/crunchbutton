<?php

class Crunchbutton_Order_Logistics extends Cana_Model
{
    const TIME_MAX_DELAY = 120; // seconds
    const TIME_BUNDLE = 600; // seconds
    const TIME_BUFFER = 2; // seconds
    const MAX_BUNDLE_SIZE = 3;
    const LOGISTICS_SIMPLE = 1;
    const LOGISTICS_COMPLEX = 2;
    const LOGISTICS_SIMPLE_ALGO_VERSION = 1;
    // Start numbering from 10K because we're using the same field for now
    const LOGISTICS_COMPLEX_ALGO_VERSION = 10001;

    const MAX_BUNDLE_TRAVEL_TIME = 10; // minutes
    const LS_MAX_UNIQUE_RESTAURANT = 3;

    const LC_MAX_MISSED_PRIORITY_ORDERS = 2;
    const LC_MAX_MISSED_PRIORITY_ORDERS_WINDOW = 180; // minutes

    const LC_MAX_NUM_ORDERS_DELTA = 4;
    const LC_MAX_NUM_UNIQUE_RESTAURANTS_DELTA = 3;
    const LC_FREE_DRIVER_BONUS = 5;

    const LC_CUTOFF_BAD_TIME = 90; // minutes
    const LC_SLACK_MAX_TIME = 120; // minutes
    const LC_HORIZON = 240; // minutes
    const LC_MAX_RUN_TIME = 5000; // milliseconds
    const LC_PENALTY_COEFFICIENT = 1.0;
    const LC_PENALTY_THRESHOLD = 45; // minutes
    const LC_CUSTOMER_DROPOFF_TIME = 5; // minutes
    const LC_RESTAURANT_PICKUP_TIME = 0; // minutes -> bundle all time into restaurant parking time for now

    const STATUS_OK = 1;
    const STATUS_ALL_OPTS_FAILED = 2;
    const STATUS_ALL_DRIVERS_LATE = 3;

    const DRIVER_OPT_SUCCESS = 1;
    const DRIVER_OPT_FAILED = 2;

    const LC_DUMMY_CLUSTER_START = 0;
    const LC_DUMMY_FAKE_CLUSTER_START = -1000; // Should be very different from LC_DUMMY_CLUSTER_START

    const LC_DEFAULT_ORDER_TIME = 10; // minutes
    const LC_DEFAULT_PARKING_TIME = 5; // minutes
    const LC_DEFAULT_SERVICE_TIME = 8; // minutes

    const LC_FAKE_ORDER_MIN_AGO = 20; // minutes
    const LC_MAX_FAKE_ORDER_AHEAD_TIME = 10; // minutes
    const LC_MAX_FAKE_ORDER_PARKING_TIME = 5; // minutes
    const LC_MAX_FAKE_ORDER_SERVICE_TIME = 5; // minutes

    const PRE_ORDER_WINDOW_SIZE = 15; // minutes TODO: Currently not used.  Maybe make penalty much harsher after this.

    public function __construct($delivery_logistics, $order, $drivers = null,
                                $distanceType = Crunchbutton_Optimizer_Input::DISTANCE_LATLON,
                                $fakeRestaurantGeo = null, $fakeCustomerGeo = null, $fakeMinAgo = null, $doCreateFakeOrders = null, $noAdjustment=false)
    {
        $this->numDriversWithPriority = -1;
        $this->numInactiveDriversWithPriority = -1;
        $this->_order = $order;
        $this->_community = $order->community();
        if (is_null($drivers)) {
            $this->_drivers = $order->getDriversToNotify();
        } else {
            $this->_drivers = $drivers;
        }
        $this->customerGeoCache = [];
        $this->_mph = null;

        if ($delivery_logistics == self::LOGISTICS_COMPLEX) {
            $this->noAdjustment = $noAdjustment;
            $this->distanceType = $distanceType;
            $this->_status = self::STATUS_OK;
            $this->_dummyClusterCounter = self::LC_DUMMY_CLUSTER_START;
            $this->_delivery_logistics = $delivery_logistics;

            $this->restaurantGeoCache = [];
            $this->restaurantParkingCache = [];
            $this->restaurantServiceCache = [];
            $this->restaurantOrderTimeCache = [];
            $this->restaurantClusterCache = [];
            $this->bundleParamsCache = [];
            $this->fakeOrder = null;
            $this->params = null;

            // Save this info for fake orders
            $this->newOrderOrderTime = null;
            $this->newOrderEarlyWindow = null;
            $this->newOrderMidWindow = null;
            $this->newOrderLateWindow = null;
            $this->newOrderParkingTime = null;
            $this->newOrderServiceTime = null;

            $this->fakeRestaurantGeo = $fakeRestaurantGeo;
            $this->fakeCustomerGeo = $fakeCustomerGeo;
            // TODO: No fake orders for now, until projections are added later
            $this->doCreateFakeOrders = false;
            if (is_null($fakeMinAgo)) {
                $this->fakeMinAgo = self::LC_FAKE_ORDER_MIN_AGO;
            } else {
                $this->fakeMinAgo = $fakeMinAgo;
            }

            $this->fillParams($this->_community);
        }
        $this->orderDistanceCache = [];
    }


    private function getNextDummyClusterNumber()
    {
        $this->_dummyClusterCounter -= 1;
        return $this->_dummyClusterCounter;
    }


    public function getTravelTime($order, $communityspeed = 10.0)
    {
        // Travel time between two customers
        if (array_key_exists($order->id_order, $this->orderDistanceCache)) {
            $traveltime = $this->orderDistanceCache[$order->id_order];
        } else {
            $o_geo = $this->getCustomerGeo($order);
            $lat = $this->order()->lat;
            $lon = $this->order()->lon;
            if (is_null($o_geo) || is_null($o_geo->lat) || is_null($o_geo->lon) || is_null($lat) ||
                is_null($lon) || is_null($communityspeed) || $communityspeed == 0
            ) {
                $traveltime = null;
            } else {
                $traveltime = Crunchbutton_GoogleGeocode::latlonDistanceInMiles($lat, $lon, $o_geo->lat, $o_geo->lon) * 60.0 / $communityspeed;
            }
            $this->orderDistanceCache[$order->id_order] = $traveltime;
        }
        return $traveltime;
    }


    public function getCustomerGeo($order)
    {
        $c_geo = null;
        if (array_key_exists($order->id_order, $this->customerGeoCache)) {
            $c_geo = $this->customerGeoCache[$order->id_order];
        } else {
            $c_geo = $order->getGeo();
            if (is_null($c_geo) || is_null($c_geo->lat) || is_null($c_geo->lon)) {
                return null;
            } else {
                $this->customerGeoCache[$order->id_order] = $c_geo;
            }
        }
        return $c_geo;
    }


    public function getRestaurantGeo($order)
    {
        $r_geo = null;
        if (array_key_exists($order->id_restaurant, $this->restaurantGeoCache)) {
            $r_geo = $this->restaurantGeoCache[$order->id_restaurant];
        } else {
            $r = $order->restaurant();
            $r_lat = $r->loc_lat;
            $r_lon = $r->loc_long;
            if (!is_null($r_lat) && !is_null($r_lon)) {
                $r_geo = new Crunchbutton_Order_Location($r_lat, $r_lon);
            } else {
                $r_geo = Crunchbutton_Restaurant::selectFakeRestaurant($order->id_community);
            }
            $this->restaurantGeoCache[$order->id_restaurant] = $r_geo;
        }
        return $r_geo;
    }

    public function getRestaurantParkingTime($order, $communityTime, $dow, $experience=1)
    {
        $r_pt = null;
        if (array_key_exists($order->id_restaurant, $this->restaurantParkingCache)) {
            $r_pt = $this->restaurantParkingCache[$order->id_restaurant];
        } else {
            $r = $order->restaurant();
            $parking = $r->parking($communityTime, $dow);
            if (!is_null($parking) && !is_null($parking->parking_duration0) && !is_null($parking->parking_duration1)
                && !is_null($parking->parking_duration2)) {
                $r_pt = [$parking->parking_duration0, $parking->parking_duration1, $parking->parking_duration2];
            } else {
                $r_pt = [self::LC_DEFAULT_PARKING_TIME, self::LC_DEFAULT_PARKING_TIME, self::LC_DEFAULT_PARKING_TIME];
            }
            $this->restaurantParkingCache[$order->id_restaurant] = $r_pt;
        }
        if ($experience < 3) {
            return $r_pt[$experience];
        } else {
            return self::LC_DEFAULT_PARKING_TIME;
        }
    }

    public function getRestaurantServiceTime($order, $communityTime, $dow, $experience=1)
    {
        $r_st = null;
        if (array_key_exists($order->id_restaurant, $this->restaurantServiceCache)) {
            $r_st = $this->restaurantServiceCache[$order->id_restaurant];
        } else {
            $r = $order->restaurant();
            $service = $r->service($communityTime, $dow);
            if (!is_null($service) && !is_null($service->service_duration0) && !is_null($service->service_duration1)
                && !is_null($service->service_duration2)) {
                $r_st = [$service->service_duration0, $service->service_duration1, $service->service_duration2];
            } else {
                $r_st = [self::LC_DEFAULT_SERVICE_TIME, self::LC_DEFAULT_SERVICE_TIME, self::LC_DEFAULT_SERVICE_TIME];
            }
            $this->restaurantServiceCache[$order->id_restaurant] = $r_st;
        }
        if ($experience < 3) {
            return $r_st[$experience];
        } else {
            return self::LC_DEFAULT_SERVICE_TIME;
        }
    }


    public function getRestaurantOrderTime($order, $communityTime, $dow)
    {
        $r_ot = null;
        if (array_key_exists($order->id_restaurant, $this->restaurantOrderTimeCache)) {
            $r_ot = $this->restaurantOrderTimeCache[$order->id_restaurant];
        } else {
            $r = $order->restaurant();
            $ordertime = $r->ordertime($communityTime, $dow);
            if (!is_null($ordertime) && !is_null($ordertime->order_time)) {
                $r_ot = $ordertime->order_time;

            } else {
                $r_ot = self::LC_DEFAULT_ORDER_TIME;
            }
            $this->restaurantOrderTimeCache[$order->id_restaurant] = $r_ot;
        }
        return $r_ot;
    }

    public function getRestaurantCluster($order, $communityTime, $dow)
    {
        $r_c = null;
        if (array_key_exists($order->id_restaurant, $this->restaurantClusterCache)) {
            $r_c = $this->restaurantClusterCache[$order->id_restaurant];
        } else {
            $r = $order->restaurant();
            $cluster = $r->cluster($communityTime, $dow);
            if (!is_null($cluster) && !is_null($cluster->id_restaurant_cluster)) {
                $r_c = $cluster->id_restaurant_cluster;
            } else {
                $r_c = $order->id_restaurant;
            }
            $this->restaurantClusterCache[$order->id_restaurant] = $r_c;
        }
        return $r_c;
    }

    public function getBundleParams($community, $bundleSize)
    {
        $bp_c = null;
        if ($bundleSize < $this->max_bundle_size) {
            if (array_key_exists($bundleSize, $this->bundleParamsCache)) {
                $bp_c = $this->bundleParamsCache[$bundleSize];
            } else {
                $bp_c = $community->logisticsBundleParams($bundleSize);
                $this->bundleParamsCache[$bundleSize] = $bp_c;
            }
        }
        return $bp_c;
    }

    public function getOrderAheadCorrection($community_speed, $order_ahead_time, $hasUnpickedupPreorder)
    {
        $correction = 0;
        if (!is_null($order_ahead_time) && !$hasUnpickedupPreorder) {
            if ($order_ahead_time > $this->order_ahead_correction_limit1 && $order_ahead_time <= $this->order_ahead_correction_limit2) {
                $correction = $this->order_ahead_correction1;
            } else if ($order_ahead_time > $this->order_ahead_correction_limit2) {
                $correction = $this->order_ahead_correction2;
            }
        }
        return $correction;
    }


    public function addOrderInfoToDestinationList($order, $isNewOrder, $isPickedUpOrder, $dlist, $communityTime, $dow, $serverDT, $experience=1)
    {
        // Add restaurant, customer pair
//        print "addOrderInfoToDestinationList\n";
        $keepFlag = true;
        $nowDate = $serverDT->format('Y-m-d H:i:s');
        $customer_geo = $this->getCustomerGeo($order);
        if (is_null($customer_geo)) {
            $keepFlag = false;
            Log::debug(['id_order' => $order->id_order, 'time' => $nowDate, 'stage' => 'addOrderInfoWithNoCustomerGeo',
                'type' => 'complexLogistics']);
        } else {
            $r_geo = $this->getRestaurantGeo($order);
            $r_pt = $this->getRestaurantParkingTime($order, $communityTime, $dow, $experience);
            $r_ordertime = $this->getRestaurantOrderTime($order, $communityTime, $dow);
            $r_cluster = $this->getRestaurantCluster($order, $communityTime, $dow);
            $r_st = $this->getRestaurantServiceTime($order, $communityTime, $dow, $experience);
        }

        if ($keepFlag) {
            if (is_null($serverDT) || is_null($order->date)) {
                $keepFlag = false;
            } else {
                $orderDT = new DateTime($order->date, new DateTimeZone(c::config()->timezone));
                $orderTime = round(($orderDT->getTimestamp() - $serverDT->getTimestamp()) / 60.0);
                $earlyWindow = max(0, $orderTime + $r_ordertime);
                $midWindow = $orderTime + Crunchbutton_Order_Logistics::LC_PENALTY_THRESHOLD;
                // TODO: Not sure we want to use the slack max time here.  Doesn't matter for now
                $lateWindow = $orderTime + Crunchbutton_Order_Logistics::LC_SLACK_MAX_TIME;

                $fakeOrderTime = (-$this->fakeMinAgo);
                $fakeOrderAheadTime = min($r_ordertime, self::LC_MAX_FAKE_ORDER_AHEAD_TIME);
                $fakeEarlyWindow = max(0, $fakeOrderTime + $fakeOrderAheadTime);
                $fakeMidWindow = $fakeOrderTime + Crunchbutton_Order_Logistics::LC_PENALTY_THRESHOLD;
                $fakeLateWindow = $fakeOrderTime + Crunchbutton_Order_Logistics::LC_SLACK_MAX_TIME;
                if ($isNewOrder) {
                    // Save this info for fake orders
                    $this->newOrderOrderTime = $fakeOrderTime;
                    $this->newOrderEarlyWindow = $fakeEarlyWindow;
                    $this->newOrderMidWindow = $fakeMidWindow;
                    $this->newOrderLateWindow = $fakeLateWindow;
                    $this->newOrderParkingTime = min($r_pt, self::LC_MAX_FAKE_ORDER_PARKING_TIME);
                    $this->newOrderServiceTime = min($r_st, self::LC_MAX_FAKE_ORDER_SERVICE_TIME);
                }
            }
        }
        if ($keepFlag) {
            if ($isPickedUpOrder) {
                // Dummy restaurant destination = customer location
                $dummyClusterNumber = $this->getNextDummyClusterNumber();
                $restaurant_destination = new Crunchbutton_Order_Logistics_Destination([
                    'objectId' => $dummyClusterNumber,
                    'type' => Crunchbutton_Order_Logistics_Destination::TYPE_RESTAURANT,
                    'geo' => $customer_geo,
                    'orderTime' => $orderTime,
                    'earlyWindow' => $earlyWindow,
                    'midWindow' => Crunchbutton_Order_Logistics::LC_HORIZON,
                    'lateWindow' => $lateWindow,
                    'restaurantParkingTime' => 0,
                    'restaurantServiceTime' => 0,
                    'cluster' => $dummyClusterNumber,
                    'isFake' => true,
                    'idOrder' => null
                ]);
            } else {
                $restaurant_destination = new Crunchbutton_Order_Logistics_Destination([
                    'objectId' => $order->id_restaurant,
                    'type' => Crunchbutton_Order_Logistics_Destination::TYPE_RESTAURANT,
                    'geo' => $r_geo,
                    'orderTime' => $orderTime,
                    'earlyWindow' => $earlyWindow,
                    'midWindow' => Crunchbutton_Order_Logistics::LC_HORIZON,
                    'lateWindow' => $lateWindow,
                    'restaurantParkingTime' => $r_pt,
                    'restaurantServiceTime' => $r_st,
                    'cluster' => $r_cluster,
                    'isFake' => false,
                    'idOrder' => $order->id_order
                ]);
            }

            $customer_destination = new Crunchbutton_Order_Logistics_Destination([
                'objectId' => $order->id_order,
                'type' => Crunchbutton_Order_Logistics_Destination::TYPE_CUSTOMER,
                'geo' => $customer_geo,
                'orderTime' => $orderTime,
                'earlyWindow' => $earlyWindow,
                'midWindow' => $midWindow,
                'lateWindow' => $lateWindow,
                'isFake' => false,
                'idOrder' => $order->id_order
            ]);
            $retVal = $dlist->addDestinationPair($restaurant_destination, $customer_destination, $isNewOrder);
            if (is_null($retVal)){
                Log::debug(['id_order' => $order->id_order, 'time' => $nowDate, 'stage' => 'errorAddingDestinationPair1',
                    'type' => 'complexLogistics']);
            } else if ($retVal === false) {
                Log::debug(['id_order' => $order->id_order, 'time' => $nowDate, 'stage' => 'errorAddingDestinationPair2',
                    'type' => 'complexLogistics']);
            }
        }
        return $keepFlag;
    }

    public function addPreorderInfoToDestinationList($order, $isNewOrder, $isPickedUpOrder, $dlist, $communityTime, $dow, $serverDT, $experience=1)
    {
        // Add restaurant, customer pair
//        print "addPreorderInfoToDestinationList\n";
        $keepFlag = true;
        $nowDate = $serverDT->format('Y-m-d H:i:s');
        $customer_geo = $this->getCustomerGeo($order);
        if (is_null($customer_geo)) {
            $keepFlag = false;
            Log::debug(['id_order' => $order->id_order, 'time' => $nowDate, 'stage' => 'addOrderInfoWithNoCustomerGeo',
                'type' => 'complexLogistics']);
        } else {
            $r_geo = $this->getRestaurantGeo($order);
            $r_pt = $this->getRestaurantParkingTime($order, $communityTime, $dow, $experience);
            $r_ordertime = $this->getRestaurantOrderTime($order, $communityTime, $dow);
            $r_cluster = $this->getRestaurantCluster($order, $communityTime, $dow);
            $r_st = $this->getRestaurantServiceTime($order, $communityTime, $dow, $experience);
        }

        if ($keepFlag) {

            if (is_null($serverDT) || is_null($order->date) || is_null($order->date_delivery)) {
                $keepFlag = false;
            } else {
                // order->date is the time that the order is sent to drivers
                $orderDT = new DateTime($order->date, new DateTimeZone(c::config()->timezone));
                $orderTime = round(($orderDT->getTimestamp() - $serverDT->getTimestamp()) / 60.0);
                // $order->date_delivery corresponds to the earliest time of the window
                $deliveryDT = new DateTime($order->date_delivery, new DateTimeZone(c::config()->timezone));
                $deliveryTime = round(($deliveryDT->getTimestamp() - $serverDT->getTimestamp()) / 60.0);

                $earlyRestaurantWindow = max(0, $orderTime + $r_ordertime);
                // Penalty starts being applied once the order is delivered after the deliveryTime
                // TODO: Maybe loosen the penalty by adding some minutes?
                $midWindow = $deliveryTime;
                $impliedOrderTime = $deliveryTime - self::LC_PENALTY_THRESHOLD;
                // TODO: Not sure we want to use the slack max time here.  Doesn't matter for now
                $lateWindow = $impliedOrderTime + Crunchbutton_Order_Logistics::LC_SLACK_MAX_TIME;

                $fakeOrderTime = (-$this->fakeMinAgo);
                $fakeOrderAheadTime = min($r_ordertime, self::LC_MAX_FAKE_ORDER_AHEAD_TIME);
                $fakeEarlyWindow = max(0, $fakeOrderTime + $fakeOrderAheadTime);
                $fakeMidWindow = $fakeOrderTime + Crunchbutton_Order_Logistics::LC_PENALTY_THRESHOLD;
                $fakeLateWindow = $fakeOrderTime + Crunchbutton_Order_Logistics::LC_SLACK_MAX_TIME;
                if ($isNewOrder) {
                    // Save this info for fake orders
                    $this->newOrderOrderTime = $fakeOrderTime;
                    $this->newOrderEarlyWindow = $fakeEarlyWindow;
                    $this->newOrderMidWindow = $fakeMidWindow;
                    $this->newOrderLateWindow = $fakeLateWindow;
                    $this->newOrderParkingTime = min($r_pt, self::LC_MAX_FAKE_ORDER_PARKING_TIME);
                    $this->newOrderServiceTime = min($r_st, self::LC_MAX_FAKE_ORDER_SERVICE_TIME);
                }
            }
        }
        if ($keepFlag) {
            if ($isPickedUpOrder) {
                // Dummy restaurant destination = customer location
                $dummyClusterNumber = $this->getNextDummyClusterNumber();
                $restaurant_destination = new Crunchbutton_Order_Logistics_Destination([
                    'objectId' => $dummyClusterNumber,
                    'type' => Crunchbutton_Order_Logistics_Destination::TYPE_RESTAURANT,
                    'geo' => $customer_geo,
                    'orderTime' => $orderTime,
                    'earlyWindow' => $earlyRestaurantWindow,
                    'midWindow' => Crunchbutton_Order_Logistics::LC_HORIZON,
                    'lateWindow' => $lateWindow,
                    'restaurantParkingTime' => 0,
                    'restaurantServiceTime' => 0,
                    'cluster' => $dummyClusterNumber,
                    'isFake' => true,
                    'idOrder' => null
                ]);
            } else {
                $restaurant_destination = new Crunchbutton_Order_Logistics_Destination([
                    'objectId' => $order->id_restaurant,
                    'type' => Crunchbutton_Order_Logistics_Destination::TYPE_RESTAURANT,
                    'geo' => $r_geo,
                    'orderTime' => $orderTime,
                    'earlyWindow' => $earlyRestaurantWindow,
                    'midWindow' => Crunchbutton_Order_Logistics::LC_HORIZON,
                    'lateWindow' => $lateWindow,
                    'restaurantParkingTime' => $r_pt,
                    'restaurantServiceTime' => $r_st,
                    'cluster' => $r_cluster,
                    'isFake' => false,
                    'idOrder' => $order->id_order
                ]);
            }

            $customer_destination = new Crunchbutton_Order_Logistics_Destination([
                'objectId' => $order->id_order,
                'type' => Crunchbutton_Order_Logistics_Destination::TYPE_CUSTOMER,
                'geo' => $customer_geo,
                'orderTime' => $impliedOrderTime,
                'earlyWindow' => $deliveryTime,
                'midWindow' => $midWindow,
                'lateWindow' => $lateWindow,
                'isFake' => false,
                'idOrder' => $order->id_order
            ]);
            $retVal = $dlist->addDestinationPair($restaurant_destination, $customer_destination, $isNewOrder);
            if (is_null($retVal)){
                Log::debug(['id_order' => $order->id_order, 'time' => $nowDate, 'stage' => 'errorAddingDestinationPair1',
                    'type' => 'complexLogistics']);
            } else if ($retVal === false) {
                Log::debug(['id_order' => $order->id_order, 'time' => $nowDate, 'stage' => 'errorAddingDestinationPair2',
                    'type' => 'complexLogistics']);
            }
        }
        return $keepFlag;
    }


    public function calculateDriverScoreCorrection($community, $community_speed, $earliestBundleMinutes, $qualifyingOrderCount,
                                                   $pickedUpFlag, $tooEarlyFlag, $nonQualifyingOrderFlag, $hasUnpickedupPreorder,
                                                   $earliestBundleMinutesIsPreorder) {

        // Don't really need $earliestBundleMinutesIsPreorder because the $tooEarlyFlag should handle situations
        //  where there are unpickedup orders that are way too in the past.
        $correction = null;
//        print "$earliestBundleMinutes, $qualifyingOrderCount, $pickedUpFlag, $tooEarlyFlag, $nonQualifyingOrderFlag\n";
        if (!$pickedUpFlag && !$tooEarlyFlag && !$nonQualifyingOrderFlag && ($qualifyingOrderCount > 0) &&
            ($qualifyingOrderCount < $this->max_bundle_size)) {
            $params = $this->getBundleParams($community, $qualifyingOrderCount);
//            var_dump($params);
            if (!is_null($params)) {
                $cutoff_at_zero = $params->cutoff_at_zero;
                $slope_per_minute = $params->slope_per_minute;
                $max_minutes = $params->max_minutes;
                $baseline_mph = $params->baseline_mph;
//                print "Cutoff at zero: $cutoff_at_zero\n";
//                print "Slope per minute: $slope_per_minute\n";
//                print "Max minutes: $max_minutes\n";
//                print "Baseline mph: $baseline_mph\n";
            } else{
                $cutoff_at_zero = Crunchbutton_Order_Logistics_Bundleparam::CUTOFF_AT_ZERO;
                $slope_per_minute = Crunchbutton_Order_Logistics_Bundleparam::SLOPE_PER_MINUTE;
                $max_minutes = Crunchbutton_Order_Logistics_Bundleparam::MAX_MINUTES;
                $baseline_mph = Crunchbutton_Order_Logistics_Bundleparam::BASELINE_MPH;
            }
            if ($earliestBundleMinutesIsPreorder && $hasUnpickedupPreorder && ($earliestBundleMinutes > $max_minutes)) {
                $earliestBundleMinutes = $max_minutes;
            }
            if (!is_null($earliestBundleMinutes) && ($earliestBundleMinutes <= $max_minutes)) {
                $correction = $cutoff_at_zero + ($earliestBundleMinutes * $slope_per_minute);
                if ($community_speed > 0) {
                    $correction = $correction * $baseline_mph / $community_speed;
                }
            }
        }

        return $correction;
    }

    public function fillParams($curCommunity) {
        $params = $curCommunity->logisticsParams(self::LOGISTICS_COMPLEX_ALGO_VERSION);
        if (!is_null($params)) {
            $this->time_max_delay = $params->time_max_delay;
            $this->time_bundle = $params->time_bundle;
            $this->max_bundle_size = $params->max_bundle_size;
            $this->max_bundle_travel_time = $params->max_bundle_travel_time;
            $this->max_num_orders_delta = $params->max_num_orders_delta;
            $this->max_num_unique_restaurants_delta = $params->max_num_unique_restaurants_delta;
            $this->free_driver_bonus = $params->free_driver_bonus;
            $this->order_ahead_correction1 = $params->order_ahead_correction1;
            $this->order_ahead_correction2 = $params->order_ahead_correction2;
            $this->order_ahead_correction_limit1 = $params->order_ahead_correction_limit1;
            $this->order_ahead_correction_limit2 = $params->order_ahead_correction_limit2;
        } else{
            $this->time_max_delay = self::TIME_MAX_DELAY;
            $this->time_bundle = self::TIME_BUNDLE;
            $this->max_bundle_size = self::MAX_BUNDLE_SIZE;
            $this->max_bundle_travel_time = self::MAX_BUNDLE_TRAVEL_TIME;
            $this->max_num_orders_delta = self::LC_MAX_NUM_ORDERS_DELTA;
            $this->max_num_unique_restaurants_delta = self::LC_MAX_NUM_UNIQUE_RESTAURANTS_DELTA;
            $this->free_driver_bonus = self::LC_FREE_DRIVER_BONUS;
            $this->order_ahead_correction1 = 5;
            $this->order_ahead_correction2 = 10;
            $this->order_ahead_correction_limit1 = 10;
            $this->order_ahead_correction_limit2 = 30;
        }
    }

    public function complexLogistics($distanceType = Crunchbutton_Optimizer_Input::DISTANCE_LATLON)
    {

        $newOrder = $this->order();
        $orderAheadTime = null;
//        $cur_id_restaurant = $newOrder->id_restaurant;
        $debug_dt = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $debugDtString = $debug_dt->format('Y-m-d H:i:s');
        Log::debug(['id_order' => $newOrder->id_order, 'time' => $debugDtString, 'stage' => 'start',
            'type' => 'complexLogistics']);
        $curCommunity = $this->_community;

        $communityCenter = $curCommunity->communityCenter();
        if (is_null($this->doCreateFakeOrders)) {
            $doCreateFakeOrders = $curCommunity->doCreateFakeOrders();
        }
        else{
            $doCreateFakeOrders = $this->doCreateFakeOrders;
        }
        $skipFlag = false;
        $skipReason = 0;
        $skipId = 0;

        // Pre-orders shouldn't make it here, but screen them out anyhow.
        if ($newOrder->preordered) {
            $skipFlag = true;
            $skipReason = 90;
        }

        $numGoodOptimizations = 0;
        $numSelectedDrivers = 0; // Number of drivers to get priority.  Should be 1, but there could be ties.
        $numSelectedInactiveDrivers = 0;

        if (is_null($communityCenter)) {
            $skipFlag = true;
            $skipReason = 1;
        } else {
            // Do this computation only if necessary
            $cur_geo = $newOrder->getGeo();
            if (is_null($cur_geo)) {
                $skipFlag = true;
                $skipReason = 2;
            }
        }

        // More negative score changes are better because score change = new total time - old total time (more or less)
        $bestScoreChangeOverall = 10000;
        $bestScoreChangeActiveDrivers = 10000;
        $numDriversWithGoodTimes = 0;  // Number of drivers who don't have orders that are late by more than n minutes.
        $numProbablyActiveDrivers = 0;
        $numProbablyInactiveDrivers = 0;

        if (!$skipFlag) {

            $new_id_order = $newOrder->id_order;
            $curCommunityTz = $curCommunity->timezone;

            $server_dt = new DateTime('now', new DateTimeZone(c::config()->timezone));
            $curCommunityDt = new DateTime('now', new DateTimeZone($curCommunityTz));
            $curCommunityTime = $curCommunityDt->format('H:i:s');
            $dow = $curCommunityDt->format('w');

            $missedMinTime = new DateTime('now', new DateTimeZone(c::config()->timezone));
            $missedMinTime->modify('- ' . self::LC_MAX_MISSED_PRIORITY_ORDERS_WINDOW . ' minutes');
            $missedMinTimeString = $missedMinTime->format('Y-m-d H:i:s');

            // Load community-specific model parameters
            $cs = $curCommunity->communityspeed($curCommunityTime, $dow);
            $orderAheadTime = $this->getRestaurantOrderTime($newOrder, $curCommunityTime, $dow);
//            print "The order ahead time is $orderAheadTime\n";
            if (is_null($cs)) {
//                print "Need to get community speed\n";
                $cs_mph = Crunchbutton_Order_Logistics_Communityspeed::DEFAULT_MPH;
            } else {
                $cs_mph = $cs->mph;
            }
            $this->_mph = $cs_mph;

            $driverCount = $this->drivers()->count();
            $driversWithNoOrdersCount = 0;
            $ordersUnfiltered = Order::deliveryOrdersByCommunity(2, $curCommunity->id_community);
            $maxMissedIndex = self::LC_MAX_MISSED_PRIORITY_ORDERS - 1;
            foreach ($this->drivers() as $driver) {
//                print "Processing driver $driver->name\n";

                $driverOrderCount = 0;  // This counts the new order and also unexpired priority orders that haven't been accepted.
                $qualifyingOrderCount = 0;
                $nonQualifyingOrderFlag = false;
                $pickedUpFlag = false;
                $tooEarlyFlag = false;
                $earliestBundleMinutes = null;
                $earliestBundleMinutesIsPreorder = false;
                $numAcceptedUndeliveredOrders = 0;  // This only counts accepted but undelivered orders
                $isProbablyInactive = false;
                $numUnpickedupPreorders = 0;
                $numUnpickedupPreordersInRange = 0;
                if (!$skipFlag) {
                    $spos = Crunchbutton_Order_Priority::lastNExpiredSpecialPriorityOrders($missedMinTimeString, $driver->id_admin, self::LC_MAX_MISSED_PRIORITY_ORDERS);
                    if ($spos->count() == self::LC_MAX_MISSED_PRIORITY_ORDERS) {
                        $sposNthMostRecentExpiration = $spos->get($maxMissedIndex)->priority_expiration;
                        $sposActionsSinceCount = Crunchbutton_Order_Priority::getNumDriverOrderActionsSince($sposNthMostRecentExpiration, $driver->id_admin);
                        if ($sposActionsSinceCount == 0) {
                            $isProbablyInactive = true;
                        }
                    }
                    $driver->__driverLocation = new Crunchbutton_Order_Logistics_DriverLocation($communityCenter);
                    $driver->__opt_status = self::DRIVER_OPT_FAILED;
                    // Get orders in the last two hours for this driver

                    $driver_geo = $communityCenter; // Default for the initial setup
//                    var_dump($driver_geo);

                    $ds = $driver->score();
                    $driver_score = $ds->score;
                    $driver_experience = $ds->experience;
                    if ($driver_score > 0) {
                        $driver_mph = $cs_mph * $driver_score;
                    } else{
                        $driver_mph = $cs_mph;
                    }
                    // Unpicked-up pre-order from the same restaurant
                    $driver->__hasUnpickedupPreorder = false;
                    $driver->__mph = $driver_mph;
                    $dlist = new Crunchbutton_Order_Logistics_DestinationList($distanceType);
                    $dlist->driverMph = $driver_mph;
                    $dlist->orderId = $new_id_order;
                    $dlist->driverId = $driver->id_admin;
                    $driver_destination = new Crunchbutton_Order_Logistics_Destination([
                        'objectId' => $driver->id_admin,
                        'type' => Crunchbutton_Order_Logistics_Destination::TYPE_DRIVER,
                        'geo' => $driver_geo,
                        'idOrder' => null
                    ]);

                    $dlist->addDriverDestination($driver_destination);

                    // Interested in any priority orders for that driver or undelivered orders for that driver or
                    //   the current order of interest

                    // Get any priority orders that have been routed to that driver in the last
                    //  n seconds
                    $priorityOrders = Crunchbutton_Order_Priority::priorityOrders($this->time_max_delay + self::TIME_BUFFER,
                        $driver->id_admin, null);

                    foreach ($ordersUnfiltered as $order) {
                        if (!$skipFlag) {

                            if ($order->id_order == $new_id_order) {
//                                print "Found this order\n";
                                // Split up for debugging
                                // Note that we screened for pre-orders earlier, so we are not screening again.
                                // TODO: Code could change if pre-orders are included somehow.
                                $checkIt = $this->addOrderInfoToDestinationList($order, true, false, $dlist,
                                    $curCommunityTime, $dow, $server_dt, $driver_experience);
                                if (!$checkIt) {
                                    $skipFlag = true;
                                    $skipReason = 3;
                                }
                                $driverOrderCount++;
                            } else {
                                $isRefunded = $order->refunded;
                                $doNotReimburseDriver = $order->do_not_reimburse_driver;
                                $isPickedUpOrder = false;
                                $addOrder = false;
                                $lastStatus = NULL;
                                $lastStatusDriver = NULL;
                                $lastStatusTimestamp = NULL;
                                $osl = $order->status()->last();
                                if ($osl && array_key_exists('status', $osl)) {
                                    $lastStatus = $osl['status'];
                                }
                                if ($osl && array_key_exists('driver', $osl)) {
                                    $lastStatusDriver = $osl['driver'];
                                }
                                if ($osl && array_key_exists('timestamp', $osl)) {
                                    // This timestamp is timezone-independent
                                    $lastStatusTimestamp = $osl['timestamp'];
                                }

                                $lastStatusAdmin = NULL;
                                if ($lastStatusDriver && array_key_exists('id_admin', $lastStatusDriver)) {
                                    $lastStatusAdmin = $lastStatusDriver['id_admin'];
                                }

                                // We care if either an undelivered order belongs to the driver, or is a priority order
                                // TODO: potential issue here if an order is canceled by CS because then
                                // the last status admin is not the driver
                                // We probably want a separate check for refunded orders
                                if ($lastStatusAdmin && $lastStatusAdmin == $driver->id_admin) {
                                    if ($lastStatus == 'pickedup' && (!$isRefunded || !$doNotReimburseDriver)) {
//                                        print "Found picked-up order\n";
                                        $isPickedUpOrder = true;
                                        $addOrder = true;
                                        $numAcceptedUndeliveredOrders++;
                                        $driver->__driverLocation->addPickedUpOrder($lastStatusTimestamp, $server_dt->getTimestamp(), $this->getRestaurantGeo($order),
                                            $order->lat, $order->lon);
                                        $pickedUpFlag = true;
                                    } else if ($lastStatus == 'accepted' && (!$isRefunded || !$doNotReimburseDriver)) {
//                                        print "Found accepted order\n";

                                        $addOrder = true;
                                        $numAcceptedUndeliveredOrders++;
                                        $driver->__driverLocation->addAcceptedOrder($lastStatusTimestamp, $server_dt->getTimestamp(),
                                            $this->getRestaurantGeo($order));
                                        $orderBundle = $this->getRestaurantCluster($order, $curCommunityTime, $dow);
                                        $curOrderBundle = $this->getRestaurantCluster($newOrder, $curCommunityTime, $dow);
//                                        $order_id_restaurant = $order->id_restaurant;

                                        if ($order->preordered) {
                                            if (!is_null($order->date_delivery)) {
                                                $numUnpickedupPreorders++;
                                                $deliveryDT = new DateTime($order->date_delivery, new DateTimeZone(c::config()->timezone));
                                                $deliveryTime = round(($deliveryDT->getTimestamp() - $server_dt->getTimestamp()) / 60.0);
                                                if ($deliveryTime <= self::LC_PENALTY_THRESHOLD) {
                                                    $numUnpickedupPreordersInRange++;
                                                    $traveltime = $this->getTravelTime($order, $driver_mph);
                                                    if (is_null($traveltime) || $traveltime < $this->max_bundle_travel_time) {
                                                        if ($orderBundle == $curOrderBundle) {
                                                            $driver->__hasUnpickedupPreorder = true;
                                                            $qualifyingOrderCount++;
                                                            $age = $deliveryTime;
                                                            if (is_null($earliestBundleMinutes) || ($age > $earliestBundleMinutes)) {
                                                                $earliestBundleMinutes = $age;
                                                                $earliestBundleMinutesIsPreorder = true;
                                                            }
                                                        }
                                                    } else {
                                                        $nonQualifyingOrderFlag = true;
                                                    }
                                                }
                                            }

                                        } else {
                                            if ($orderBundle == $curOrderBundle) {
                                                $serverTimestamp = $server_dt->getTimeStamp();
                                                if ($lastStatusTimestamp && $lastStatusTimestamp + $this->time_bundle > $serverTimestamp) {
                                                    $traveltime = $this->getTravelTime($order, $driver_mph);
                                                    if (is_null($traveltime) || $traveltime < $this->max_bundle_travel_time) {
                                                        $qualifyingOrderCount++;
                                                        $age = ($serverTimestamp - $lastStatusTimestamp) / 60.0;
                                                        if (is_null($earliestBundleMinutes) || ($age > $earliestBundleMinutes)) {
                                                            $earliestBundleMinutes = $age;
                                                            $earliestBundleMinutesIsPreorder = false;
                                                        }
//                            Log::debug(['id_order' => $newOrder->id_order, 'time' => $nowDate, 'driver' => $driver->id_admin,
//                                'stage' => 'accepted_order_counted', 'order_check'=>$order->id_order,
//                                'type' => 'simpleLogistics']);
                                                    } else {
                                                        $nonQualifyingOrderFlag = true;
                                                    }
                                                } else {
                                                    // The driver accepted an order from the restaurant earlier than the time window.
                                                    //  Assumption is he's got the food and bundling doesn't help him.
                                                    $tooEarlyFlag = true;
//                        Log::debug(['id_order' => $newOrder->id_order, 'time' => $nowDate, 'driver' => $driver->id_admin,
//                            'stage' => 'too early flag', 'order_check'=>$order->id_order,
//                            'type' => 'simpleLogistics']);
                                                }
                                            }
                                        }
                                    } else if ($lastStatus == 'delivered') {
//                                        print "Found delivered order\n";
                                        $addOrder = false;
                                        $driver->__driverLocation->addDeliveredOrder($lastStatusTimestamp, $server_dt->getTimestamp(),
                                            $order->lat, $order->lon, $this->getRestaurantGeo($order));
                                    }
                                } else if ($lastStatus == 'new' && (!$isRefunded || !$doNotReimburseDriver) && Crunchbutton_Order_Priority::checkOrderInArray($order->id_order, $priorityOrders)) {
//                                    print "Found new priority order\n";
                                    // Pre-orders have no priority right now, so we don't need to worry about them
                                    $addOrder = true;
                                    $orderBundle = $this->getRestaurantCluster($order, $curCommunityTime, $dow);
                                    $curOrderBundle = $this->getRestaurantCluster($newOrder, $curCommunityTime, $dow);
                                    if ($orderBundle == $curOrderBundle) {
                                        $serverTimestamp = $server_dt->getTimeStamp();
                                        if ($lastStatusTimestamp && $lastStatusTimestamp + $this->time_bundle > $serverTimestamp) {
                                            $traveltime = $this->getTravelTime($order, $driver_mph);
                                            if (is_null($traveltime) || $traveltime < $this->max_bundle_travel_time) {
                                                $qualifyingOrderCount++;
                                                $age = ($serverTimestamp - $lastStatusTimestamp) / 60.0;
                                                if (is_null($earliestBundleMinutes) || ($age > $earliestBundleMinutes)) {
                                                    $earliestBundleMinutes = $age;
                                                    $earliestBundleMinutesIsPreorder = false;
                                                }
//                            Log::debug(['id_order' => $newOrder->id_order, 'time' => $nowDate, 'driver' => $driver->id_admin,
//                                'stage' => 'accepted_order_counted', 'order_check'=>$order->id_order,
//                                'type' => 'simpleLogistics']);
                                            }
                                        }
                                    }
                                }

                                if ($addOrder) {
                                    // Split up for debugging
                                    if ($order->preordered) {
                                        $checkIt = $this->addPreorderInfoToDestinationList($order, false,
                                            $isPickedUpOrder, $dlist, $curCommunityTime, $dow, $server_dt, $driver_experience);
                                        if (!$checkIt) {
                                            $skipFlag = true;
                                            $skipReason = 40;
                                            $skipId = $order->id_order;
                                        }
                                        $driverOrderCount++;
                                    } else {
                                        $checkIt = $this->addOrderInfoToDestinationList($order, false,
                                            $isPickedUpOrder, $dlist, $curCommunityTime, $dow, $server_dt, $driver_experience);
                                        if (!$checkIt) {
                                            $skipFlag = true;
                                            $skipReason = 4;
                                            $skipId = $order->id_order;
                                        }
                                        $driverOrderCount++;
                                    }
                                }
                            }
                        }

                    }
                    if (!$skipFlag) {
                        $driver->__driverLocation->determineDriverGeo($driver, $server_dt);
                        $dlist->updateDriverDestinationGeo($driver->__driverLocation);
                        $driver->__dlist = $dlist;
                    }

                }
                // Order count needs to be at least 1 since the order itself is counted
                $adjustedDriverOrderCount = $driverOrderCount - ($numUnpickedupPreorders - $numUnpickedupPreordersInRange);
                if ($adjustedDriverOrderCount <= 1) {
                    $driversWithNoOrdersCount++;
                    $driver->__hasNoOrders = true;
                } else{
                    $driver->__hasNoOrders = false;
                }
                $driver->__qualifyingOrderCount = $qualifyingOrderCount;
                $driver->__pickedUpFlag = $pickedUpFlag;
                $driver->__tooEarlyFlag = $tooEarlyFlag;
                $driver->__nonQualifyingOrderFlag = $nonQualifyingOrderFlag;
                $driver->__earliestBundleMinutes = $earliestBundleMinutes;
                $driver->__earliestBundleMinutesIsPreorder = $earliestBundleMinutesIsPreorder;
                $driver->__numAcceptedUndeliveredOrders = $numAcceptedUndeliveredOrders;
                $driver->__isProbablyInactive = $isProbablyInactive;
                $driver->__numUnpickedupPreorders = $numUnpickedupPreorders;
                $driver->__numUnpickedupPreordersInRange = $numUnpickedupPreordersInRange;
                if ($isProbablyInactive) {
                    $numProbablyInactiveDrivers++;
                } else{
                    $numProbablyActiveDrivers++;
                }
            }

            if (!$skipFlag) {
//                print "Driver counts: $driversWithNoOrdersCount $driverCount\n";
                if ($driversWithNoOrdersCount == $driverCount) {
                    $doCreateFakeOrders = false;
                    $noFreeDriverBonus = true;
                } else{
                    $noFreeDriverBonus = false;
                }

                $minCorrection = null;
                foreach ($this->drivers() as $driver) {
                    $qualifyingOrderCount = $driver->__qualifyingOrderCount;
                    $pickedUpFlag = $driver->__pickedUpFlag;
                    $tooEarlyFlag = $driver->__tooEarlyFlag;
                    $earliestBundleMinutes = $driver->__earliestBundleMinutes;
                    $earliestBundleMinutesIsPreorder = $driver->__earliestBundleMinutesIsPreorder;
                    $nonQualifyingOrderFlag = $driver->__nonQualifyingOrderFlag;
                    $driverCorrection = $this->calculateDriverScoreCorrection($curCommunity, $driver->__mph,
                        $earliestBundleMinutes,
                        $qualifyingOrderCount, $pickedUpFlag, $tooEarlyFlag, $nonQualifyingOrderFlag,
                        $driver->__hasUnpickedupPreorder, $driver->__earliestBundleMinutesIsPreorder);
                    if (!is_null($driverCorrection)) {
                        $orderAheadCorrection = $this->getOrderAheadCorrection($driver->mph, $orderAheadTime, $driver->__hasUnpickedupPreorder,
                            $earliestBundleMinutesIsPreorder);
                        $totalCorrection = $driverCorrection + $orderAheadCorrection;

                        if (is_null($minCorrection) || ($totalCorrection < $minCorrection)) {
                            $minCorrection = $totalCorrection;
                        }

                        $driver->__tempCorrection = $totalCorrection;

                    } else {
                        $driver->__tempCorrection = null;
                    }
                }
                foreach ($this->drivers() as $driver) {
                    if (is_null($minCorrection) || is_null($driver->__tempCorrection)) {
                        $scoreCorrection = 0;
                        if (is_null($minCorrection) && !$noFreeDriverBonus && $driver->__hasNoOrders){
                            $scoreCorrection = $this->free_driver_bonus;
                        }
                    } else{
                        $scoreCorrection = $minCorrection;
                    }

                    $dlist = $driver->__dlist;

//                    print "Now run the optimizations\n";
                    // Run the optimization for each driver here
                    // Run once without the new order, if needed
//                    print "Do create fake orders: $doCreateFakeOrders\n";
                    if ($doCreateFakeOrders) {
                        if (is_null($this->fakeOrder)) {
                            $this->fakeOrder = new Crunchbutton_Order_Logistics_FakeOrder(self::LC_DUMMY_FAKE_CLUSTER_START,
                                $curCommunity, $this->newOrderOrderTime, $this->newOrderEarlyWindow,
                                $this->newOrderMidWindow, $this->newOrderLateWindow, $this->newOrderParkingTime, $this->newOrderServiceTime);
                            if (!is_null($this->fakeRestaurantGeo)) {
                                $this->fakeOrder->fakeRestaurantGeo = $this->fakeRestaurantGeo;
                            }
                            if (!is_null($this->fakeCustomerGeo)) {
                                $this->fakeOrder->fakeCustomerGeo = $this->fakeCustomerGeo;
                            }
                        }
                    }
                    $dicts = $dlist->createOptimizerInputs($this->fakeOrder, $doCreateFakeOrders);
                    $dOld = $dicts['old']; // without new order
                    $dNew = $dicts['new']; // with new order
                    $dNumOldNodes = $dicts['numOldNodes'];
                    $dNumNewNodes = $dicts['numNewNodes'];
                    $dNewNodeOrderIds = $dicts['newNodeOrderIds'];
                    $dNewFakes = $dicts['newFakes'];
                    $hasFakeOrder = $dicts['hasFakeOrder'];
                    if (!is_null($dNew)) {
                        if (!$hasFakeOrder && $dlist->hasOnlyNewOrder()) {
                            // Nothing to optimize in the original, so we create a dummy result
                            $resultOld = (object)['resultType' => Crunchbutton_Optimizer_Result::RTYPE_OK, 'score' => 0];
                        } else {
                            $rOld = Crunchbutton_Optimizer::optimize($dOld);
                            $resultOld = new Crunchbutton_Optimizer_Result($rOld);
                        }

                        // Run with the new order
                        $rNew = Crunchbutton_Optimizer::optimize($dNew);
                        if (!is_null($rNew)) {
                            $resultNew = new Crunchbutton_Optimizer_Result($rNew);
                            if (($resultOld->resultType == Crunchbutton_Optimizer_Result::RTYPE_OK) &&
                                ($resultNew->resultType == Crunchbutton_Optimizer_Result::RTYPE_OK) &&
                                !is_null($resultOld->score) && !is_null($resultNew->score)
                            ) {
                                $numGoodOptimizations += 1;
                                $driver->__opt_status = self::DRIVER_OPT_SUCCESS;
                                if ($this->noAdjustment) {
                                    $useScoreCorrection = 0;
                                } else {
                                    $useScoreCorrection = $scoreCorrection;
                                }
                                $scoreChange = $resultNew->score - ($resultOld->score + $useScoreCorrection);
                                $driver->__scoreChange = $scoreChange;
                                if ($scoreChange < $bestScoreChangeOverall) {
                                    $bestScoreChangeOverall = $scoreChange;
                                }
                                if (!$driver->__isProbablyInactive) {
                                    if ($scoreChange < $bestScoreChangeActiveDrivers) {
                                        $bestScoreChangeActiveDrivers = $scoreChange;
                                    }
                                }
                                if ($resultNew->numBadTimes == 0 || $hasFakeOrder) {
                                    $numDriversWithGoodTimes += 1;
                                }

                                $resultNew->saveRouteToDb($resultNew->resultType, $new_id_order, $driver->id_admin, $server_dt,
                                    $dNew, $dNewNodeOrderIds, $dNewFakes);

                            } else {
                                $resultNew->saveRouteToDb($resultNew->resultType, $new_id_order, $driver->id_admin, $server_dt, $dNew);
                            }
                        } else{
                            Log::debug(['id_order' => $newOrder->id_order, 'time' => $debugDtString, 'stage' => 'nullResultNew',
                                'numOldNodes' => $dNumOldNodes,
                                'numNewNodes' => $dNumNewNodes, 'adminId' => $skipId, 'type' => 'complexLogistics']);
                        }
                    } else {
                        $skipFlag = true;
                        $skipId = $driver->id_admin;
//                        if (is_null($dlist)) {
//                            $skipReason = 5;
//                        } else{
//                            $skipReason = 50;
//                        }
//                        Log::debug(['id_order' => $newOrder->id_order, 'time' => $serverDate, 'stage' => 'skipReason',
//                            'numOldNodes' => $dNumOldNodes,
//                            'numNewNodes' => $dNumNewNodes, 'adminId' => $skipId, 'type' => 'complexLogistics']);
                    }
                }
                if ($skipFlag) {
                    // Remove routes
                    Crunchbutton_Order_Logistics_Route::q('SELECT * FROM order_logistics_route WHERE id_order = ?', [$new_id_order])->delete();
                }
            }

            if ($this->_status == self::STATUS_OK && $numDriversWithGoodTimes == 0) {
                $this->_status = self::STATUS_ALL_DRIVERS_LATE;
            }

            if ($numGoodOptimizations == 0) {
                $skipFlag = true;
                $skipReason = 6;
                $this->_status = self::STATUS_ALL_OPTS_FAILED;
            } else if (!$skipFlag) {
                // Look for the best driver
//                print("Best Score change overall: $bestScoreChangeOverall\n");
//                print("Best Score change active drivers: $bestScoreChangeActiveDrivers\n");
                foreach ($this->drivers() as $driver) {
                    $driver->__priority = false;
                    if ($driver->__opt_status = self::DRIVER_OPT_SUCCESS) {
                        // Get the score
                        if ($driver->__isProbablyInactive) {
                            if ($driver->__scoreChange == $bestScoreChangeOverall) {
                                $driver->__priority = true;
                                $numSelectedDrivers += 1;
                                $numSelectedInactiveDrivers += 1;
                            }
                        } else if ($numProbablyInactiveDrivers > 0){
                            // Probably active driver, get the second place driver(s) and add to the priority list
                            if ($driver->__scoreChange == $bestScoreChangeActiveDrivers) {
                                $driver->__priority = true;
                                $numSelectedDrivers += 1;
                            }
                        } else {
                            // No inactive drivers, proceed as normal
                            if ($driver->__scoreChange == $bestScoreChangeOverall) {
                                $driver->__priority = true;
                                $numSelectedDrivers += 1;
                            }
                        }
                    }
                }
//                print("Num selected Drivers: $numSelectedDrivers\n");
//                print("Num selected Inactive Drivers: $numSelectedInactiveDrivers\n");
            }
        }

        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $nowDate = $now->format('Y-m-d H:i:s');

        // Make sure that it really is expired, but adding a buffer
        $now2 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now2->modify('- ' . self::TIME_BUFFER . ' seconds');
        $nowDate2 = $now2->format('Y-m-d H:i:s');

        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $later->modify('+ ' . $this->time_max_delay . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');
        // Give the selected driver the order immediately, without delay.
        //  Other drivers get the delay.

        // If there are a large amount of drivers, it's more efficient to
        //  restructure all of this with a single loop instead of a loop + second array search.
        // Either use $drivers or a hash table lookup instead.

        // IMPORTANT: The code in Crunchbutton_Order::deliveryOrdersForAdminOnly assumes that the priority
        //  expiration for a particular order is the same for drivers.
        $this->numDriversWithPriority = $numSelectedDrivers;
        $this->numInactiveDriversWithPriority = $numSelectedInactiveDrivers;
//        Log::debug(['id_order' => $newOrder->id_order, 'time' => $nowDate, 'stage' => 'before_op_create',
//            'type' => 'complexLogistics']);
        if ($skipFlag || $numSelectedDrivers == 0) {
            $num_drivers_with_priority = 0;
//            Log::debug(['id_order' => $newOrder->id_order, 'time' => $nowDate, 'stage' => 'skipFlag',
//                'skipReason' => $skipReason, 'skipId' => $skipId, 'type' => 'complexLogistics']);

        } else{
            $num_drivers_with_priority = $numSelectedDrivers;
        }

        foreach ($this->drivers() as $driver) {
            if ($skipFlag) {
                $driver->__seconds = 0;
                $driver->__priority = false;
                $priority_given = Crunchbutton_Order_Priority::PRIORITY_NO_ONE;
                $seconds_delay = 0;
                $priority_expiration = $nowDate2;
                $num_undelivered_orders = -1;
            } else {
//            print "Cur order:".$cur_id_order."\n";
                if ($numSelectedDrivers > 0) {
                    if ($driver->__priority) {
                        $driver->__seconds = 0;
                        $priority_given = Crunchbutton_Order_Priority::PRIORITY_HIGH;
                        $seconds_delay = 0;
                        $priority_expiration = $laterDate;
                    } else {
                        $driver->__seconds = $this->time_max_delay;
                        $priority_given = Crunchbutton_Order_Priority::PRIORITY_LOW;
                        $seconds_delay = $this->time_max_delay;
                        $priority_expiration = $laterDate;
                    }
                } else {
                    $driver->__seconds = 0;
                    $driver->__priority = false;
                    $priority_given = Crunchbutton_Order_Priority::PRIORITY_NO_ONE;
                    $seconds_delay = 0;
                    $priority_expiration = $nowDate2;
                }
                $num_undelivered_orders = $driver->__numAcceptedUndeliveredOrders;

            }
            $priority = new Crunchbutton_Order_Priority([
                'id_order' => $newOrder->id_order,
                'id_restaurant' => $newOrder->id_restaurant,
                'id_admin' => $driver->id_admin,
                'priority_time' => $nowDate,
                'priority_algo_version' => self::LOGISTICS_COMPLEX_ALGO_VERSION,
                'priority_given' => $priority_given,
                'num_undelivered_orders' => $num_undelivered_orders,
                'num_drivers_with_priority' => $num_drivers_with_priority,
                'seconds_delay' => $seconds_delay,
                'is_probably_inactive' => $driver->__isProbablyInactive,
                'num_unpickedup_preorders' => $driver->__numUnpickedupPreorders,
                'num_unpickedup_pos_in_range' => $driver->__numUnpickedupPreordersInRange,
                'num_orders_bundle_check' => $driver->__qualifyingOrderCount,
                'priority_expiration' => $priority_expiration
            ]);
            $priority->save();
        }
        $debug_dt = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $debugDtString = $debug_dt->format('Y-m-d H:i:s');
        Log::debug(['id_order' => $newOrder->id_order, 'time' => $debugDtString, 'stage' => 'after_op_create',
            'type' => 'complexLogistics']);
    }

    public function simpleLogistics()
    {

        $time = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $cur_id_restaurant = $this->order()->id_restaurant;
        $cur_id_order = $this->order()->id_order;

        $driverOrderCounts = [];
        $driverUniqueRestaurantCounts = [];

        $curCommunity = $this->_community;
        $curCommunityTz = $curCommunity->timezone;
        $curCommunityDt = new DateTime('now', new DateTimeZone($curCommunityTz));
        $curCommunityTime = $curCommunityDt->format('H:i:s');
        $dow = $curCommunityDt->format('w');
        // Load community-specific model parameters
        $cs = $curCommunity->communityspeed($curCommunityTime, $dow);
        if (is_null($cs)) {
//                print "Need to get community speed\n";
            $cs_mph = Crunchbutton_Order_Logistics_Communityspeed::DEFAULT_MPH;
        } else {
            $cs_mph = $cs->mph;
        }
//        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
//        $nowDate = $now->format('Y-m-d H:i:s');
        // Add the now criteria to deal with simulation / historical studies
        $ordersUnfiltered = Order::deliveryOrdersByCommunityBeforeNow(2, $curCommunity->id_community);
        foreach ($this->drivers() as $driver) {
            // Get orders in the last two hours for this driver

            // Get priority orders that have been routed to that driver in the last
            //  n seconds for that restaurant
            $priorityOrders = Crunchbutton_Order_Priority::priorityOrdersBeforeNow(self::TIME_MAX_DELAY + self::TIME_BUFFER,
                $driver->id_admin, $cur_id_restaurant);
            $uniqueRestaurants = [];
            $acceptCount = 0;
            $tooEarlyFlag = false;
            foreach ($ordersUnfiltered as $order) {
                // Don't count this order

                if ($order->id_order == $cur_id_order) {
                    continue;
                }
                $isRefunded = $order->refunded;
                $doNotReimburseDriver = $order->do_not_reimburse_driver;
                $lastStatus = NULL;
                $lastStatusDriver = NULL;
                $lastStatusTimestamp = NULL;
                $osl = $order->status()->last();
                if ($osl && array_key_exists('status', $osl)) {
                    $lastStatus = $osl['status'];
                }
                if ($osl && array_key_exists('driver', $osl)) {
                    $lastStatusDriver = $osl['driver'];
                }
                if ($osl && array_key_exists('timestamp', $osl)) {
                    $lastStatusTimestamp = $osl['timestamp'];
                }

                $lastStatusAdmin = NULL;
                if ($lastStatusDriver && array_key_exists('id_admin', $lastStatusDriver)) {
                    $lastStatusAdmin = $lastStatusDriver['id_admin'];
                }
                // if the order is another drivers, or already delivered or canceled, we don't care
                if ($lastStatusAdmin && ($lastStatusAdmin != $driver->id_admin ||
                        $lastStatus == 'delivered' || $lastStatus == 'canceled')
                ) {
//                    Log::debug(['id_order' => $newOrder->id_order, 'time' => $nowDate,
//                        'last_driver_with_action'=> $lastStatusAdmin,
//                        'last_status' => $lastStatus, 'driver' => $driver->id_admin,
//                        'stage' => 'ignored_order', 'order_check'=>$order->id_order,
//                        'type' => 'simpleLogistics']);
                    continue;
                } else if ($lastStatusAdmin && ($lastStatusAdmin == $driver->id_admin &&
                        $lastStatus == 'pickedup' && (!$isRefunded || !$doNotReimburseDriver))
                ) {
//                    Log::debug(['id_order' => $newOrder->id_order, 'time' => $nowDate,
//                        'last_driver_with_action'=> $lastStatusAdmin,
//                        'last_status' => $lastStatus, 'driver' => $driver->id_admin,
//                        'stage' => 'ignored_order', 'order_check'=>$order->id_order,
//                        'type' => 'simpleLogistics']);
                    // TODO: Why are drivers who bundle not penalized for picked-up orders?
                    $uniqueRestaurants[$order->id_restaurant] = 1;
                } else if ($lastStatusAdmin && ($lastStatusAdmin == $driver->id_admin &&
                        $lastStatus == 'accepted' && (!$isRefunded || !$doNotReimburseDriver))
                ) {
//                    Log::debug(['id_order' => $newOrder->id_order, 'time' => $nowDate, 'driver' => $driver->id_admin,
//                        'stage' => 'accepted_order', 'order_check'=>$order->id_order,
//                        'type' => 'simpleLogistics']);
                    // Count accepted orders that have happened in the last n minutes
                    $uniqueRestaurants[$order->id_restaurant] = 1;
                    if ($order->id_restaurant == $cur_id_restaurant) {
                        if ($order->preordered && !is_null($order->date_delivery)) {
                            // If pre-order is due to be delivered in less than n minutes and
                            //  it has been accepted, but not picked-up, then bundle.
                            // TODO: This algo could use some improvement
                            $deliveryDT = new DateTime($order->date_delivery, new DateTimeZone(c::config()->timezone));
                            $deliveryTime = round(($deliveryDT->getTimestamp() - $time->getTimestamp()) / 60.0);
                            if ($deliveryTime <= self::LC_PENALTY_THRESHOLD) {
                                $acceptCount++;
                            }
                        } else {
                            if ($lastStatusTimestamp && $lastStatusTimestamp + self::TIME_BUNDLE > $time->getTimeStamp()) {
                                $traveltime = $this->getTravelTime($order, $cs_mph);
                                if (is_null($traveltime) || $traveltime < self::MAX_BUNDLE_TRAVEL_TIME) {
                                    $acceptCount++;
//                            Log::debug(['id_order' => $newOrder->id_order, 'time' => $nowDate, 'driver' => $driver->id_admin,
//                                'stage' => 'accepted_order_counted', 'order_check'=>$order->id_order,
//                                'type' => 'simpleLogistics']);
                                }
                            } else {
                                // The driver accepted an order from the restaurant earlier than the time window.
                                //  Assumption is he's very close to getting the food and bundling doesn't help him,
                                //   meaning that he won't have time to deal with a new order from the same restaurant
                                $tooEarlyFlag = true;
//                        Log::debug(['id_order' => $newOrder->id_order, 'time' => $nowDate, 'driver' => $driver->id_admin,
//                            'stage' => 'too early flag', 'order_check'=>$order->id_order,
//                            'type' => 'simpleLogistics']);
                            }
                        }
                    }
                } else if ($lastStatus == 'new' && (!$isRefunded || !$doNotReimburseDriver) && Crunchbutton_Order_Priority::checkOrderInArray($order->id_order, $priorityOrders)) {
                    // Interested in new orders if they show up in the priority list with the top priority
                    //  and these haven't expired yet.
                    if ($order->id_restaurant == $cur_id_restaurant) {
                        $traveltime = $this->getTravelTime($order, $cs_mph);
                        if (is_null($traveltime) || $traveltime < self::MAX_BUNDLE_TRAVEL_TIME) {
                            $acceptCount++;
//                        Log::debug(['id_order' => $newOrder->id_order, 'time' => $nowDate, 'driver' => $driver->id_admin,
//                            'stage' => 'new_order_counted', 'order_check'=>$order->id_order,
//                            'type' => 'simpleLogistics']);
                        }
//                    else{
//                        Log::debug(['id_order' => $newOrder->id_order, 'time' => $nowDate, 'driver' => $driver->id_admin,
//                            'stage' => 'new_order_not_counted', 'order_check'=>$order->id_order,
//                            'type' => 'simpleLogistics']);
//                    }
                    }
                }

            }
            if ($tooEarlyFlag) {
                $acceptCount = 0;
            }
            $driver->__acceptCount = $acceptCount;
            $driverOrderCounts[$driver->id_admin] = $acceptCount;
            $driverUniqueRestaurantCounts[$driver->id_admin] = count($uniqueRestaurants);

        } // End driver loop
        // Use an array here in the case of ties
        $selectedDriverIds = [];
        $busyDrivers = [];
        $availableDrivers = [];

        foreach ($driverUniqueRestaurantCounts as $idAdmin => $restaurantCount) {
//            $test = $driverOrderCounts[$idAdmin];
//            print "Restaurant count: $restaurantCount\n";
//            print "Driver order count: $test\n";

            if ($restaurantCount > self::LS_MAX_UNIQUE_RESTAURANT && $driverOrderCounts[$idAdmin] == 0){
                // Too many restaurants and no bundling
                $busyDrivers[] = $idAdmin;
            } else{
                $availableDrivers[] = $idAdmin;
            }
        }
        $numAvailableDrivers = count($availableDrivers);
//        print "Number available drivers: $numAvailableDrivers\n";
        $minAcceptCount = NULL;
        $minUniqueRestaurantCount = NULL;
        if ($numAvailableDrivers == 1) {
            $selectedDriverIds[] = $availableDrivers[0];
        } else {

            if ($numAvailableDrivers == 0) {
                $availableDrivers = $busyDrivers;
            }
            foreach ($availableDrivers as $idAdmin){
                $acceptCount = $driverOrderCounts[$idAdmin];
                if ($acceptCount > 0 && $acceptCount < self::MAX_BUNDLE_SIZE) {
                    if (is_null($minAcceptCount) || $acceptCount <= $minAcceptCount) {
                        $minAcceptCount = $acceptCount;
                    }
                }
                $restaurantCount = $driverUniqueRestaurantCounts[$idAdmin];
                if (is_null($minUniqueRestaurantCount) || $restaurantCount <= $minUniqueRestaurantCount) {
                    $minUniqueRestaurantCount = $restaurantCount;
                }

            }
            if ($minAcceptCount == 0){
                // Break the tie based on fewest number of unique restaurants
                // TODO: Break tie based on fewest number of orders?

                foreach ($availableDrivers as $idAdmin) {
                    $restaurantCount = $driverUniqueRestaurantCounts[$idAdmin];
                    if ($restaurantCount == $minUniqueRestaurantCount) {
                        $selectedDriverIds[] = $idAdmin;
                    }
                }
            } else{
                // Give order to driver with the fewest number of orders to that restaurant > 0
                foreach ($availableDrivers as $idAdmin) {
                    $orderCount = $driverOrderCounts[$idAdmin];
                    if ($orderCount == $minAcceptCount) {
                        $selectedDriverIds[] = $idAdmin;
                    }
                }

            }
        }
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $nowDate = $now->format('Y-m-d H:i:s');

        // Make sure that it really is expired, but adding a buffer
        $now2 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now2->modify('- ' . self::TIME_BUFFER . ' seconds');
        $nowDate2 = $now2->format('Y-m-d H:i:s');

        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('+ ' . self::TIME_MAX_DELAY . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');
        // Give the selected driver the order immediately, without delay.
        //  Other drivers get the delay.

        // If there are a large amount of drivers, it's more efficient to
        //  restructure all of this with a single loop instead of a loop + second array search.
        // Either use $drivers or a hash table lookup instead.
        $this->numDriversWithPriority = count($selectedDriverIds);
        foreach ($this->drivers() as $driver) {
//            print "Cur order:".$cur_id_order."\n";
            if ($this->numDriversWithPriority > 0) {
                if (in_array($driver->id_admin, $selectedDriverIds)) {
                    $driver->__seconds = 0;
                    $driver->__priority = true;
                    $priority_given = Crunchbutton_Order_Priority::PRIORITY_HIGH;
                    $seconds_delay = 0;
                    $priority_expiration = $laterDate;
                } else {
                    $driver->__seconds = self::TIME_MAX_DELAY;
                    $driver->__priority = false;
                    $priority_given = Crunchbutton_Order_Priority::PRIORITY_LOW;
                    $seconds_delay = self::TIME_MAX_DELAY;
                    $priority_expiration = $laterDate;
                }
            } else {
                $driver->__seconds = 0;
                $driver->__priority = false;
                $priority_given = Crunchbutton_Order_Priority::PRIORITY_NO_ONE;
                $seconds_delay = 0;
                $priority_expiration = $nowDate2;
            }
            $priority = new Crunchbutton_Order_Priority([
                'id_order' => $cur_id_order,
                'id_restaurant' => $cur_id_restaurant,
                'id_admin' => $driver->id_admin,
                'priority_time' => $nowDate,
                'priority_algo_version' => self::LOGISTICS_SIMPLE_ALGO_VERSION,
                'priority_given' => $priority_given,
                'seconds_delay' => $seconds_delay,
                'priority_expiration' => $priority_expiration
            ]);

            $priority->save();
        }
    }

    public function process()
    {
        $dl = $this->_delivery_logistics;
        if ($dl == self::LOGISTICS_COMPLEX) {
            $this->complexLogistics($this->distanceType);
        } else {
            // Default to simple
            $this->simpleLogistics();
        }
    }


    public function drivers()
    {
        return $this->_drivers;
    }

    public function order()
    {
        if (!isset($this->_order) && (isset($this->id_order))) {
            $this->_order = Order::o($this->id_order);
        }
        return $this->_order;
    }
}