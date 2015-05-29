<?php

class Crunchbutton_Order_Logistics_DestinationList extends Cana_Model {
	public function __construct() {
		$this->_destinations = [];
	}
	
	public function add($destination = null, $force = false) {
		if (!$destination) {
			return false;
		}

		if (!$force) {
			foreach ($this->destinations() as $dst) {
				if ($destination->status == 'progress' && $dst->address == $destination->address) {
					return false;
				}
			}
		}

		$this->_destinations[] = $destination;

		return true;
	}

	public function calculateEach($driver) {
		// loop through each current destination, and then the next destinations of the unaccepted orders
		$progressDestinations = [];
		$newDestinations = [];
		
		$destinationLists = [];
		$destinationResults = [];

		foreach ($this->destinations() as $destination) {
			if ($destination->status == 'progress') {
				$progressDestinations[] = $destination;
			} elseif ($destination->status == 'new' && $destination->type == 'customer') {
				$newDestinations[] = $destination;
			}
		}
		
		foreach ($newDestinations as $destination) {
			$destinationList = new Crunchbutton_Order_Logistics_DestinationList;
			foreach ($progressDestinations as $d) {
				$destinationList->add($d);
			}

			$destinationList->add($destination);
			$destinationList->add($this->getRestaurantByCustomer($destination));
			
			$destinationLists[] = $destinationList;
		}
		
		
		foreach ($destinationLists as $id => $list) {
			$l = [];
			foreach ($list->destinations() as $destination) {
				$l[] = $destination->address;
			}

			$distance = $driver->distance($l);
			$destinationResults[$id] = $distance;
		}
		
		return ['lists' => $destinationLists, 'results' => $destinationResults];
	}
	
	public function getClosest($driver) {
		$results = $this->calculateEach($driver);
		$lowest = null;
		foreach ($results['results'] as $id => $res) {
			if (is_null($lowest) || $res->time < $lowest) {
				$lowest = $id;
			}
		}
		return $results['lists'][$lowest];
	}
	
	public function getOrderId() {
		foreach ($this->destinations() as $destination) {
			if ($destination == 'new') {
				return $destination->id_order;
			}
		}
	}
	
	public function getRestaurantByCustomer($customer) {
		foreach ($this->destinations() as $destination) {
			if ($destination->type == 'restaurant' && $destination->id_order == $customer->id_order) {
				return $destination;
			}
		}
		return false;
	}
	
	public function destinations() {
		return $this->_destinations;
	}
}