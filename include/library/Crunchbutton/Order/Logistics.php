<?php

class Crunchbutton_Order_Logistics extends Cana_Model {
	const TIME_MAX_DELAY = '120'; // seconds
	const MAX_DESTINATIONS = '3'; // unique destinations

	public function __construct($order Crunchbutton_Order, $drivers) {
		$this->_order = $order;
		$this->_drivers = $order->getDriversToNotify();
		$this->process();
	}
	
	public function process() {
		$order->status()->last()
		
		foreach ($drivers as $driver) {
			$orders = Order::deliveryOrders(12, false, $driver);
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