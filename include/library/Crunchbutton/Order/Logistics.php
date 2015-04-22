<?php

class Crunchbutton_Order_Logistics extends Cana_Model {
	const TIME_MAX_DELAY = 120; // seconds
	const MAX_DESTINATIONS = 3; // unique destinations
	const TIME_BUNDLE = 10 * 60; // seconds

	public function __construct($order) {
		$this->_order = $order;
		$this->_drivers = $order->getDriversToNotify();
		$this->process();
	}
	
	public function process() {
		
		$time = time();
		$driverPrios = [];
		$driverVolumes = [];

		foreach ($this->drivers() as $driver) {

			$prios[$driver->id_admin] = [];
			$acceptedRestaurants = [];
			$destinations = [];
			
			// get the drivers valid location based on an expiration time
			$location = $driver->location();
			if (!$location->valid()) {
				$location = null;
			}

			$ordersUnfiltered = Order::deliveryOrders(12, false, $driver);
			
			// get some information about the orders list
			// we need to know which restaurants they have already accepted orders at first
			foreach ($ordersUnfiltered as $order) {

				// if the order is another drivers, or already delivered, we dont care
				if ($order->status->last()->driver->id_admin && ($order->status->last()->driver->id_admin != $dirver->id_admin || $order->status->last()->status == 'delivered')) {
					continue;
				}
				
				$driverPrios[$driver->id_admin][$order->id_order] = 0;

				// get a list of accepted restaurants so we can bundle unaccepted orders
				if ($order->status->last()->status == 'accepted') {
					$orderTime = strtotime($order->date);
					
					// only bundle them if it is within the bundle time limit
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
					'address' => $order->address
				]);
				$destinations = Crunchbutton_Order_Logistics_Destination::($destinations, $customer);
				
				$restaurant = new Crunchbutton_Order_Logistics_Destination([
					'address' => $order->restaurant()->address
				]);
				// force add a destination if its not bundled
				$destinations = Crunchbutton_Order_Logistics_Destination::($destinations, $restaurant, $prios[$driver->id_admin][$order->id_order] ? false : true);


				echo 'Order #'.$order->id_order.' - status: '.json_encode($order->status()->last())."\n";
			}
			
			$driverDestinations[$driver->id_admin] = $destinations;
		}
		
		// calculate the current max destinations to get a ratio
		$maxDestinations = 0;
		foreach ($driverDestinations as $dst) {
			if (count($dst) > $maxDestinations) {
				$maxDestinations = count($dst);
			}
		}
		
		// now that we have all our data we need to assign priorties
		
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