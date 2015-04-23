<?php

class Crunchbutton_Order_Logistics extends Cana_Model {
	const TIME_MAX_DELAY = 120; // seconds
	const MAX_DESTINATIONS = 3; // unique destinations
	const TIME_BUNDLE = 600; // seconds
	const CALCULATE_CURRENT_ORDER_ONLY = true; // dont try to plan routes with other orders that have not been acepted

	public function __construct($order) {
		$this->_order = $order;
		$this->_drivers = $order->getDriversToNotify();
		$this->process();
	}
	
	public function process() {
		
		$time = time();
		$driverPrios = [];
		$driverVolumes = [];
		$driverOrders = [];

		foreach ($this->drivers() as $driver) {

			$prios[$driver->id_admin] = [];
			$acceptedRestaurants = [];
			$destinations = new Crunchbutton_Order_Logistics_DestinationList;
			
			// get the drivers valid location based on an expiration time
			$location = $driver->location();
			if ($location && !$location->valid()) {
				$location = null;
			}

			$ordersUnfiltered = Order::deliveryOrders(12, false, $driver);
			
			// get some information about the orders list
			// we need to know which restaurants they have already accepted orders at first
			foreach ($ordersUnfiltered as $order) {

				// if the order is another drivers, or already delivered, we dont care
				if ($order->status()->last()->driver->id_admin && ($order->status()->last()->driver->id_admin != $dirver->id_admin || $order->status()->last()->status == 'delivered')) {
					continue;
				}
				
				// skip all orders that arent the one being run in this queue. they will run in their own queue job
				if (self::CALCULATE_CURRENT_ORDER_ONLY && $order->status()->last()->status == 'new' && $order->id_order != $this->order()->id_order) {
					continue;
				}
				
				$driverPrios[$driver->id_admin][$order->id_order] = 0;

				// get a list of accepted restaurants so we can bundle unaccepted orders
				if ($order->status()->last()->status == 'accepted') {
					$orderTime = strtotime($order->date);
					
					// only bundle them if it is within the bundle time limit
					// @todo: there should be 2 bundle times, so that it counts the time of the oldest order for that restaurant, not just newest
					//   that way the diver deosnt stay at chipotle forever
					if ($orderTime + self::TIME_BUNDLE < $time) {
						$acceptedRestaurants[$order->restaurant()->id_restaurant] = $order;
					}
				}
				$orders[] = $order;
			}
			
			$driverVolumes[$driver->id_admin] = count($orders);

			foreach ($orders as $order) {
				
				// bundle priority
				if (array_key_exists($order->restaurant()->id_restaurant, $acceptedRestaurants)) {
					$driverPrios[$driver->id_admin][$order->id_order] = 1;
				}

				$customer = new Crunchbutton_Order_Logistics_Destination([
					'address' => $order->address,
					'from' => $location,
					'driver' => $driver,
					'type' => 'customer',
					'status' => $order->status()->last()->status == 'new' ? 'new' : 'progress',
					'id_order' => $order->id_order
				]);
				$destinations->add($customer);
				
				$restaurant = new Crunchbutton_Order_Logistics_Destination([
					'address' => $order->restaurant()->address,
					'from' => $location,
					'driver' => $driver,
					'type' => 'restaurant',
					'status' => $order->status()->last()->status == 'new' ? 'new' : 'progress',
					'id_order' => $order->id_order
				]);
				$destinations->add($restaurant);

				if ($location) {
					// add small amount just for having location
					// this way drivers without location enabled do not have preference on anything other than bundled
					$driverPrios[$driver->id_admin][$order->id_order] += .1;
				}

				echo 'Order #'.$order->id_order.' - status: '.json_encode($order->status()->last())."\n";
			}
			
			$driverOrders[$driver->id_admin] = $orders;
			$driverDestinations[$driver->id_admin] = $destinations;
		}
		
		// assign a score based on travel time of the restaurant and driver + their current orders
		foreach ($this->drivers() as $driver) {
			// calculate each possible destination set with the drivers current orders
			$destinationCalculations = $driverDestinations[$driver->id_admin]->calculateEach($driver);

			foreach ($destinationCalculations['results'] as $id => $result) {
				$idOrder = $destinationCalculations['list'][$id]->getOrderId();
				$time = $result->time;

				// assign score. formula is just bullshit right now
				$driverPrios[$driver->id_admin][$idOrder] += (1 - ($time / 200));
			}
		}
		
		// calculate the current max destinations to get a ratio
		$maxDestinations = 0;
		foreach ($driverDestinations as $dst) {
			if (count($dst) > $maxDestinations) {
				$maxDestinations = count($dst);
			}
		}
		
		// now that we have all our data we need to assign priorties by fliping our matrix
		
		foreach ($driverPrios as $idAdmin => $orders) {
			foreach ($orders as $idOrder => $prio) {
			}
		}
		
		// perform priority calculations
		foreach ($this->drivers() as $driver) {
			
		}
		
		$this->_drivers = $drivers;
	}

	
	public function drivers() {
		return $this->_drivers;
	}
	
	public function order() {
		if (!isset($this->_order)) {
			$this->_order = Order::o($this->id_order);
		}
	}
}