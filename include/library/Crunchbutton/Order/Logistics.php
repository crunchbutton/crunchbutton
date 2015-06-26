<?php

class Crunchbutton_Order_Logistics extends Cana_Model {
	const TIME_MAX_DELAY = 120; // seconds
	const TIME_BUNDLE = 600; // seconds
    const TIME_BUFFER = 2; // seconds
    const MAX_BUNDLE_SIZE = 5;
    const PRIORITY_ALGO_VERSION = 1;

	public function __construct($order) {
		$this->_order = $order;
		$this->_drivers = $order->getDriversToNotify();
		$this->process();
	}
	
	public function process() {
		
		$time = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $cur_id_restaurant = $this->order()->id_restaurant;
        $cur_id_order = $this->order()->id_order;

        // Route to drivers who have the fewest accepted orders for that restaurant, greater than 0.
        $minAcceptCount = NULL;
        $driverOrderCounts = [];

        foreach ($this->drivers() as $driver) {
            // Get orders in the last hour for this driver
            $ordersUnfiltered = Order::deliveryOrders(1, false, $driver);

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
                if ($osl && array_key_exists('status', $osl)){
                    $lastStatus = $osl['status'];
                }
                if ($osl && array_key_exists('driver', $osl)){
                    $lastStatusDriver = $osl['driver'];
                }
                if ($osl && array_key_exists('timestamp', $osl)){
                    $lastStatusTimestamp = $osl['timestamp'];
                }

                $lastStatusAdmin = NULL;
                if ($lastStatusDriver && array_key_exists('id_admin', $lastStatusDriver)){
                    $lastStatusAdmin = $lastStatusDriver['id_admin'];
                }
                // if the order is another drivers, or already delivered or picked up, we don't care
                if ($lastStatusAdmin && ($lastStatusAdmin != $driver->id_admin ||
                        $lastStatus == 'delivered' || $lastStatus == 'pickedup')) {
                    continue;
                }

                if ($lastStatus == 'accepted'){
                    // Count accepted orders that have happened in the last n minutes
                    // This won't work properly if the earlier filters for restaurant and such are not implemented

                    if ($lastStatusTimestamp && $lastStatusTimestamp + self::TIME_BUNDLE > $time->getTimeStamp()) {
                        $acceptCount++;
                    } else{
                        // The driver accepted an order from the restaurant earlier than the time window.
                        //  Assumption is he's got the food and bundling doesn't help him.
                        $tooEarlyFlag = true;
                    }
                }
                else if ($lastStatus == 'new' && Order_Priority::checkOrderInArray($order->id_order, $priorityOrders)) {
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
            if ($orderCount == $minAcceptCount){
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
            if (count($selectedDriverIds)){
                if (in_array($driver->id_admin, $selectedDriverIds)){
                    $driver->__seconds = 0;
                    $priority_given = Crunchbutton_Order_Priority::PRIORITY_HIGH;
                    $seconds_delay = 0;
                    $priority_expiration = $laterDate;
                }
                else{
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
                'priority_algo_version' => self::PRIORITY_ALGO_VERSION,
                'priority_given' => $priority_given,
                'seconds_delay' => $seconds_delay,
                'priority_expiration' => $priority_expiration
            ]);

            $priority->save();
		}
	}

	
	public function drivers() {
		return $this->_drivers;
	}
	
	public function order() {
		if (!isset($this->_order)) {
			$this->_order = Order::o($this->id_order);
		}
        return $this->_order;
	}
}