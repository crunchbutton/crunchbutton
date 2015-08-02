<?php

class Crunchbutton_Order_Logistics extends Cana_Model
{
    const TIME_MAX_DELAY = 120; // seconds
    const TIME_BUNDLE = 600; // seconds
    const TIME_BUFFER = 2; // seconds
    const MAX_BUNDLE_SIZE = 5;
    const LOGISTICS_SIMPLE = 1;
    const LOGISTICS_COMPLEX = 2;
    const LOGISTICS_SIMPLE_ALGO_VERSION = 1;
    // Start numbering from 10K because we're using the same field for now
    const LOGISTICS_COMPLEX_ALGO_VERSION = 10000;

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

    const LC_MAX_DRIVER_NO_LOC = 2400; // seconds = 40 minutes * 60

    public function __construct($delivery_logistics, $order, $drivers = null, $distanceType=Crunchbutton_Optimizer_Input::DISTANCE_LATLON)
    {

        $this->_order = $order;
        if (is_null($drivers)) {
            $this->_drivers = $order->getDriversToNotify();
        } else {
            $this->_drivers = $drivers;
        }

        if ($delivery_logistics == self::LOGISTICS_COMPLEX) {
            $this->distanceType = $distanceType;
            $this->_status = self::STATUS_OK;
            $this->_dummyClusterCounter = self::LC_DUMMY_CLUSTER_START;
            $this->_delivery_logistics = $delivery_logistics;
            $this->restaurantGeoCache = [];
            $this->restaurantParkingCache = [];
            $this->restaurantOrderTimeCache = [];
            $this->restaurantClusterCache = [];
            $this->fakeOrder = null;

            // Save this info for fake orders
            $this->newOrderOrderTime = null;
            $this->newOrderEarlyWindow = null;
            $this->newOrderMidWindow = null;
            $this->newOrderLateWindow = null;
            $this->newOrderParkingTime = null;
        }
        $this->process();
    }


    private function getNextDummyClusterNumber() {
        $this->_dummyClusterCounter -= 1;
        return $this->_dummyClusterCounter;
    }

    public function getDriverLocation($driver, $serverDT, $maxDiff, $communityCenter)
    {
        // TODO: Last location isn't necessarily good.  Nor does it necessarily exist.
        // TODO: Use location of last action
        // Use last location if known for now
        // Otherwise community center to simplify things
        $location = $driver->location();
        if (!is_null($location) && !is_null($location->date) && !is_null($location->lat) &&
            !is_null($location->lon)){
            $driverDT = new DateTime($location->date, new DateTimeZone(c::config()->timezone));
            $diff = $serverDT->getTimestamp() - $driverDT->getTimestamp();
            if ($diff < $maxDiff){
                $returnLoc = new Crunchbutton_Order_Location($location->lat, $location->lon);
                $returnLoc->dt = $driverDT;
                return $returnLoc;
            }

        }
        // Otherwise community center
        return $communityCenter;

    }

    public function getRestaurantGeo($order) {
        $r_geo = null;
        if (array_key_exists($order->id_restaurant, $this->restaurantGeoCache)) {
            $r_geo = $this->restaurantGeoCache[$order->id_restaurant];
        }
        else{
            $r = $order->restaurant();
            $r_lat = $r->loc_lat;
            $r_lon = $r->loc_long;
            if (!is_null($r_lat) && !is_null($r_lon)) {
                $r_geo = new Crunchbutton_Order_Location($r_lat, $r_lon);
                $this->restaurantGeoCache[$order->id_restaurant] = $r_geo;
            }
        }
        return $r_geo;
    }

    public function getRestaurantParkingTime($order, $communityTime, $dow) {
        $r_pt = null;
        if (array_key_exists($order->id_restaurant, $this->restaurantParkingCache)) {
            $r_pt = $this->restaurantParkingCache[$order->id_restaurant];
        }
        else{
            $r = $order->restaurant();
            $parking = $r->parking($communityTime, $dow);
            if (!is_null($parking) && !is_null($parking->parking_duration)) {
                $r_pt = $parking->parking_duration;
                $this->restaurantParkingCache[$order->id_restaurant] = $r_pt;
            }
        }
        return $r_pt;
    }

    public function getRestaurantOrderTime($order, $communityTime, $dow) {
        $r_ot = null;
        if (array_key_exists($order->id_restaurant, $this->restaurantOrderTimeCache)) {
            $r_ot = $this->restaurantOrderTimeCache[$order->id_restaurant];
        }
        else{
            $r = $order->restaurant();
            $ordertime = $r->ordertime($communityTime, $dow);
            if (!is_null($ordertime) && !is_null($ordertime->order_time)) {
                $r_ot = $ordertime->order_time;
                $this->restaurantOrderTimeCache[$order->id_restaurant] = $r_ot;
            }
        }
        return $r_ot;
    }

    public function getRestaurantCluster($order, $communityTime, $dow) {
        $r_c = null;
        if (array_key_exists($order->id_restaurant, $this->restaurantClusterCache)) {
            $r_c = $this->restaurantClusterCache[$order->id_restaurant];
        }
        else{
            $r = $order->restaurant();
            $cluster = $r->cluster($communityTime, $dow);
            if (!is_null($cluster) && !is_null($cluster->id_restaurant_cluster)) {
                $r_c = $cluster->id_restaurant_cluster;
                $this->restaurantClusterCache[$order->id_restaurant] = $r_c;
            }
        }
        return $r_c;
    }

    public function addOrderInfoToDestinationList($order, $isNewOrder, $isPickedUpOrder, $dlist, $communityTime, $dow, $serverDT) {
        // Add restaurant, customer pair
//        print "addOrderInfoToDestinationList\n";
        $keepFlag = true;

        $customer_geo = $order->getGeo();
        $r_geo = null;
        $r_pt = null;
        $r_ordertime = null;
        $r_cluster = null;
        if (!is_null($customer_geo)) {
            $r_geo = $this->getRestaurantGeo($order);
            if (is_null($r_geo)){
//                print "Failed here 1\n";
                $keepFlag = false;
            }
            else{
                // Ugly nesting!!!!
                $r_pt = $this->getRestaurantParkingTime($order, $communityTime, $dow);
                if (is_null($r_pt)){
//                    print "Failed here 2\n";
                    $keepFlag = false;
                }
                else{
                    $r_ordertime = $this->getRestaurantOrderTime($order, $communityTime, $dow);
                    if (is_null($r_ordertime)){
//                        print "Failed here 3\n";
                        $keepFlag = false;
                    }
                    else{
                        $r_cluster = $this->getRestaurantCluster($order, $communityTime, $dow);
                        if (is_null($r_cluster)){
//                            print "Failed here 4\n";
                            $keepFlag = false;
                        }

                    }
                }
            }
        } else{
//            print "Failed here 0\n";
            $keepFlag = false;
        }

        if ($keepFlag) {
            if (is_null($serverDT) || is_null($order->date)) {
                $keepFlag = false;
            } else {
                $orderDT = new DateTime($order->date, new DateTimeZone(c::config()->timezone));
                $orderTime = round(($serverDT->getTimestamp() - $orderDT->getTimestamp()) / 60.0);
                $earlyWindow = max(0, $orderTime + $r_ordertime);
                $midWindow = $orderTime + Crunchbutton_Order_Logistics::LC_PENALTY_THRESHOLD;
                // TODO: Not sure we want to use the slack max time here.  Doesn't matter for now
                $lateWindow = $orderTime + Crunchbutton_Order_Logistics::LC_SLACK_MAX_TIME;
                if ($isNewOrder) {
                    // Save this info for fake orders
                    $this->newOrderOrderTime = $orderTime;
                    $this->newOrderEarlyWindow = $earlyWindow;
                    $this->newOrderMidWindow =  $midWindow;
                    $this->newOrderLateWindow = $lateWindow;
                    $this->newOrderParkingTime = $r_pt;
                }
            }
        }
        if ($keepFlag) {
            if ($isPickedUpOrder) {
                // Dummy restaurant destination = customer location
                $dummyClusterNumber = $this->getNextDummyClusterNumber();
                $restaurant_destination = new Crunchbutton_Order_Logistics_Destination([
                    'objectId' => 0,
                    'type' => Crunchbutton_Order_Logistics_Destination::TYPE_RESTAURANT,
                    'geo' => $customer_geo,
                    'orderTime' => $orderTime,
                    'earlyWindow' => $earlyWindow,
                    'midWindow' => Crunchbutton_Order_Logistics::LC_HORIZON,
                    'lateWindow' => $lateWindow,
                    'restaurantParkingTime' => 0,
                    'cluster' => $dummyClusterNumber,
                    'isFake' => true
                ]);
            }
            else{
                $restaurant_destination = new Crunchbutton_Order_Logistics_Destination([
                    'objectId' => $order->id_restaurant,
                    'type' => Crunchbutton_Order_Logistics_Destination::TYPE_RESTAURANT,
                    'geo' => $r_geo,
                    'orderTime' => $orderTime,
                    'earlyWindow' => $earlyWindow,
                    'midWindow' => Crunchbutton_Order_Logistics::LC_HORIZON,
                    'lateWindow' => $lateWindow,
                    'restaurantParkingTime' => $r_pt,
                    'cluster' => $r_cluster,
                    'isFake' => false
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
                'isFake' => false
            ]);
            $dlist->addDestinationPair($restaurant_destination, $customer_destination, $isNewOrder);
        }
        return $keepFlag;
    }


    public function complexLogistics($distanceType = Crunchbutton_Optimizer_Input::DISTANCE_LATLON)
    {
        $newOrder = $this->order();

        $curCommunity = $newOrder->community();
        $communityCenter = $curCommunity->communityCenter();
        $doCreateFakeOrders = $curCommunity->doCreateFakeOrders();

        $skipFlag = false;

        $numGoodOptimizations = 0;
        $numSelectedDrivers = 0; // Number of drivers to get priority.  Should be 1, but there could be ties.

        if (is_null($communityCenter)){
            $skipFlag = true;
        } else{
            // Do this computation only if necessary
//            print "Found community center\n";
            $cur_geo = $newOrder->getGeo();
            if (is_null($cur_geo)){
                $skipFlag = true;
            }
        }

        $bestScoreChange = -10000;
        $numDriversWithGoodTimes = 0;  // Number of drivers who don't have orders that are late by more than n minutes.

        if (!$skipFlag) {
            $new_id_restaurant = $newOrder->id_restaurant;
            $new_id_order = $newOrder->id_order;
            $curCommunityTz = $curCommunity->timezone;

            $server_dt = new DateTime('now', new DateTimeZone(c::config()->timezone));
            $curCommunityDt = new DateTime('now', new DateTimeZone($curCommunityTz));
            $curCommunityTime = $curCommunityDt->format('H:i:s');
            $dow = $curCommunityDt->format('w');
            // Load community-specific model parameters
            $cs = $curCommunity->communityspeed($curCommunityTime, $dow);
            if (is_null($cs)){
//                print "Need to get community speed\n";
                $cs = Crunchbutton_Order_Logistics_Communityspeed::DEFAULT_MPH;
            }

            // Get this order info:
            // Note: Moved out of here because the driver node needs to go first.
            // TODO: Rewrite code to handle this out of order
//            $skipFlag = $skipFlag || (!$this->addOrderInfoToDestinationList($newOrder, $baseDlist));

            $driverCount = $this->drivers()->count();
            $driversWithNoOrdersCount = 0;
            foreach ($this->drivers() as $driver) {
//                print "Processing driver $driver->name\n";

                $driverOrderCount = 0;
                if (!$skipFlag) {
                    $driver->_opt_status = self::DRIVER_OPT_FAILED;
                    // Get orders in the last two hours for this driver
                    $ordersUnfiltered = Order::deliveryOrders(2, false, $driver);
                    $driver_geo = $this->getDriverLocation($driver, $server_dt, self::LC_MAX_DRIVER_NO_LOC, $communityCenter);
//                    var_dump($driver_geo);

//                    $driver_score = $driver->score();
                    //  TODO: Adjust mph to adjust for distances not being quite straight line.
                    $driver_mph = $cs->mph;
                    $dlist = new Crunchbutton_Order_Logistics_DestinationList($distanceType);
                    $dlist->driverMph = $driver_mph;
                    $dlist->orderId = $new_id_order;
                    $dlist->driverId = $driver->id_admin;
                    $driver_destination = new Crunchbutton_Order_Logistics_Destination([
                        'objectId' => $driver->id_admin,
                        'type' => Crunchbutton_Order_Logistics_Destination::TYPE_DRIVER,
                        'geo' => $driver_geo
                    ]);

                    $dlist->addDriverDestination($driver_destination);

                    // Interested in any priority orders for that driver or undelivered orders for that driver or
                    //   the current order of interest

                    // Get any priority orders that have been routed to that driver in the last
                    //  n seconds
                    $priorityOrders = Crunchbutton_Order_Priority::priorityOrders(self::TIME_MAX_DELAY + self::TIME_BUFFER,
                        $driver->id_admin, null);
                    foreach ($ordersUnfiltered as $order) {
                        if (!$skipFlag) {

                            if ($order->id_order == $new_id_order) {
//                                print "Found this order\n";
                                // Split up for debugging
                                $checkIt = $this->addOrderInfoToDestinationList($order, true, false, $dlist,
                                    $curCommunityTime, $dow, $server_dt);
                                $skipFlag = $skipFlag || (!$checkIt);
                                $driverOrderCount++;
                            }
                            else {
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
                                    $lastStatusTimestamp = $osl['timestamp'];
                                }

                                $lastStatusAdmin = NULL;
                                if ($lastStatusDriver && array_key_exists('id_admin', $lastStatusDriver)) {
                                    $lastStatusAdmin = $lastStatusDriver['id_admin'];
                                }

                                // We care if either an undelivered order belongs to the driver, or is a priority order
                                if ($lastStatusAdmin && $lastStatusAdmin == $driver->id_admin) {
                                    if ($lastStatus == 'pickedup') {
//                                        print "Found picked-up order\n";
                                        $isPickedUpOrder = true;
                                        $addOrder = true;
                                    }
                                    else if ($lastStatus == 'accepted') {
//                                        print "Found accepted order\n";
                                        $addOrder = true;
                                    }
                                }
                                else if ($lastStatus == 'new' && Order_Priority::checkOrderInArray($order->id_order, $priorityOrders)){
//                                    print "Found new priority order\n";
                                    $addOrder = true;
                                }

                                if ($addOrder) {
                                    // Split up for debugging
                                    $checkIt = $this->addOrderInfoToDestinationList($order, false,
                                            $isPickedUpOrder, $dlist, $curCommunityTime, $dow, $server_dt);
                                    $skipFlag = $skipFlag || (!$checkIt);
                                    $driverOrderCount++;
                                }
                            }
                        }

                    }

                    $driver->dlist = $dlist;

                }
                if ($driverOrderCount <=1){
                    $driversWithNoOrdersCount++;
                }
            }

            if (!$skipFlag) {
//                print "Driver counts: $driversWithNoOrdersCount $driverCount\n";
                if ($driversWithNoOrdersCount == $driverCount){
                    $doCreateFakeOrders = false;
                }

                foreach ($this->drivers() as $driver) {

                    $dlist = $driver->dlist;
//                    print "Now run the optimizations\n";
                    // Run the optimization for each driver here
                    // Run once without the new order, if needed
                    if ($doCreateFakeOrders) {
                        if (is_null($this->fakeOrder)) {
                            $this->fakeOrder = new Crunchbutton_Order_Logistics_FakeOrder(self::LC_DUMMY_FAKE_CLUSTER_START,
                                $curCommunity, $this->newOrderOrderTime, $this->newOrderEarlyWindow,
                                $this->newOrderMidWindow, $this->newOrderLateWindow, $this->newOrderParkingTime);
                        }
                    }
                    $dicts = $dlist->createOptimizerInputs($this->fakeOrder, $doCreateFakeOrders);
                    $dOld = $dicts['old']; // without new order
                    $dNew = $dicts['new']; // with new order
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
                        $resultNew = new Crunchbutton_Optimizer_Result($rNew);

                        if (($resultOld->resultType == Crunchbutton_Optimizer_Result::RTYPE_OK) &&
                            ($resultNew->resultType == Crunchbutton_Optimizer_Result::RTYPE_OK) &&
                            !is_null($resultOld->score) && !is_null($resultNew->score)
                        ) {
                            $numGoodOptimizations += 1;
                            $driver->_opt_status = self::DRIVER_OPT_SUCCESS;
                            $scoreChange = $resultNew->score - $resultOld->score;
                            $driver->_scoreChange = $scoreChange;
                            if ($scoreChange > $bestScoreChange) {
                                $bestScoreChange = $scoreChange;
                            }
                            if ($resultNew->numBadTimes == 0 || $hasFakeOrder) {
                                $numDriversWithGoodTimes += 1;
                            }

                        }
                    }
                    else{
                        $skipFlag = true;
                    }
                }
            }


            if ($this->_status == self::STATUS_OK && $numDriversWithGoodTimes == 0) {
                $this->_status = self::STATUS_ALL_DRIVERS_LATE;
            }

            if ($numGoodOptimizations == 0) {
                $skipFlag = true;
                $this->_status = self::STATUS_ALL_OPTS_FAILED;
            } else if (!$skipFlag){
                // Look for the best driver
                foreach ($this->drivers() as $driver) {
                    $driver->_priority = false;
                    if ($driver->_opt_status = self::DRIVER_OPT_SUCCESS) {
                        // Get the score
                        if ($driver->_scoreChange == $bestScoreChange) {
                            $driver->_priority = true;
                            $numSelectedDrivers += 1;
                        }
                    };
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

        // IMPORTANT: The code in Crunchbutton_Order::deliveryOrdersForAdminOnly assumes that the priority
        //  expiration for a particular order is the same for drivers.
        foreach ($this->drivers() as $driver) {
            if ($skipFlag) {
                $driver->__seconds = 0;
                $priority_given = Crunchbutton_Order_Priority::PRIORITY_NO_ONE;
                $seconds_delay = 0;
                $priority_expiration = $nowDate2;
            } else {
//            print "Cur order:".$cur_id_order."\n";
                if ($numSelectedDrivers > 0) {
                    if ($driver->_priority) {
                        $driver->__seconds = 0;
                        $priority_given = Crunchbutton_Order_Priority::PRIORITY_HIGH;
                        $seconds_delay = 0;
                        $priority_expiration = $laterDate;
                    } else {
                        $driver->__seconds = self::TIME_MAX_DELAY;
                        $priority_given = Crunchbutton_Order_Priority::PRIORITY_LOW;
                        $seconds_delay = self::TIME_MAX_DELAY;
                        $priority_expiration = $laterDate;
                    }
                } else {
                    $driver->__seconds = 0;
                    $priority_given = Crunchbutton_Order_Priority::PRIORITY_NO_ONE;
                    $seconds_delay = 0;
                    $priority_expiration = $nowDate2;
                }

            }
            $priority = new Crunchbutton_Order_Priority([
                'id_order' => $newOrder->id_order,
                'id_restaurant' => $newOrder->id_restaurant,
                'id_admin' => $driver->id_admin,
                'priority_time' => $nowDate,
                'priority_algo_version' => self::LOGISTICS_COMPLEX_ALGO_VERSION,
                'priority_given' => $priority_given,
                'seconds_delay' => $seconds_delay,
                'priority_expiration' => $priority_expiration
            ]);
            $priority->save();
        }
    }

    public function simpleLogistics()
    {
        $time = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $cur_id_restaurant = $this->order()->id_restaurant;
        $cur_id_order = $this->order()->id_order;

        // Route to drivers who have the fewest accepted orders for that restaurant, greater than 0.
        $minAcceptCount = NULL;
        $driverOrderCounts = [];

        foreach ($this->drivers() as $driver) {
            // Get orders in the last two hours for this driver
            $ordersUnfiltered = Order::deliveryOrders(2, false, $driver);

            // Get priority orders that have been routed to that driver in the last
            //  n seconds for that restaurant
            $priorityOrders = Crunchbutton_Order_Priority::priorityOrders(self::TIME_MAX_DELAY + self::TIME_BUFFER,
                $driver->id_admin, $cur_id_restaurant);
            $acceptCount = 0;
            $tooEarlyFlag = false;
            foreach ($ordersUnfiltered as $order) {
                // Don't count this order
                //  Redundant check with the 'new' check below, but this check is cheaper
                // MVP: We only care about the restaurant corresponding to the order restaurant
                // This could be added to the order query directly, but I'm leaving it
                //  as is for future iterations where we need all of the restaurants.

                if ($order->id_order == $cur_id_order || $order->id_restaurant != $cur_id_restaurant) {
                    continue;
                }

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
                // if the order is another drivers, or already delivered or picked up, we don't care
                if ($lastStatusAdmin && ($lastStatusAdmin != $driver->id_admin ||
                        $lastStatus == 'delivered' || $lastStatus == 'pickedup')
                ) {
                    continue;
                }

                if ($lastStatus == 'accepted') {
                    // Count accepted orders that have happened in the last n minutes
                    // This won't work properly if the earlier filters for restaurant and such are not implemented

                    if ($lastStatusTimestamp && $lastStatusTimestamp + self::TIME_BUNDLE > $time->getTimeStamp()) {
                        $acceptCount++;
                    } else {
                        // The driver accepted an order from the restaurant earlier than the time window.
                        //  Assumption is he's got the food and bundling doesn't help him.
                        $tooEarlyFlag = true;
                    }
                } else if ($lastStatus == 'new' && Order_Priority::checkOrderInArray($order->id_order, $priorityOrders)){
                    // Interested in new orders if they show up in the priority list with the top priority
                    //  and these haven't expired yet.
                    // This won't work properly if the earlier filters for restaurant and such are not implemented
                    $acceptCount++;
                }

            }
            if ($tooEarlyFlag) {
                $acceptCount = 0;
            }
            if ($acceptCount > 0 && $acceptCount < self::MAX_BUNDLE_SIZE) {
                if (is_null($minAcceptCount) || $acceptCount <= $minAcceptCount) {
                    // Don't care about drivers that have more than the current min
                    $driverOrderCounts[$driver->id_admin] = $acceptCount;
                    $minAcceptCount = $acceptCount;
                }
            }
        }
        // Use an array here in the case of ties
        $selectedDriverIds = [];

        foreach ($driverOrderCounts as $idAdmin => $orderCount) {
            if ($orderCount == $minAcceptCount) {
                $selectedDriverIds[] = $idAdmin;
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
        foreach ($this->drivers() as $driver) {
//            print "Cur order:".$cur_id_order."\n";
            if (count($selectedDriverIds)) {
                if (in_array($driver->id_admin, $selectedDriverIds)) {
                    $driver->__seconds = 0;
                    $priority_given = Crunchbutton_Order_Priority::PRIORITY_HIGH;
                    $seconds_delay = 0;
                    $priority_expiration = $laterDate;
                } else {
                    $driver->__seconds = self::TIME_MAX_DELAY;
                    $priority_given = Crunchbutton_Order_Priority::PRIORITY_LOW;
                    $seconds_delay = self::TIME_MAX_DELAY;
                    $priority_expiration = $laterDate;
                }
            } else {
                $driver->__seconds = 0;
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