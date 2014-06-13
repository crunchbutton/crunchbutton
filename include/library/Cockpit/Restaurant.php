<?php

class Cockpit_Restaurant extends Crunchbutton_Restaurant {

	public function __construct($id = null) {
		$this->_changeSetName = 'Crunchbutton_Restaurant';
		parent::__construct($id);
	}

	// Settlement stuff

	// get the last payment
	public function getLastPayment(){
		if (!isset($this->_lastPayment)) {
			$this->_lastPayment = Payment::q('select * from payment where id_restaurant="'.$this->id_restaurant.'" order by date desc limit 1')->get(0);
		}
		return $this->_lastPayment;
	}

	// get the last sent payment
	public function sendPayment($filters = []) {
		if (!isset($this->_lastPayment)) {
			$this->_lastPayment = Payment::q('select * from payment where id_restaurant="'.$this->id_restaurant.'" order by date desc limit 1')->get(0);
		}
		return $this->_lastPayment;
	}

	// get orders that are payable; not test, within our date range, it just return the order, the calc are made at settlement class
	public function payableOrders($filters = []) {

		if (!isset($this->_payableOrders)) {
			$q = 'SELECT * FROM `order`
							WHERE id_restaurant="'.$this->id_restaurant.'"
								AND DATE(`date`) >= "' . (new DateTime($filters['start']))->format('Y-m-d') . '"
								AND DATE(`date`) <= "' . (new DateTime($filters['end']))->format('Y-m-d') . '"
								AND NAME NOT LIKE "%test%"
							ORDER BY `pay_type` ASC, `date` ASC ';
			$orders = Order::q($q);
			$this->_payableOrders = $orders;
		}
		return $this->_payableOrders;
	}

}