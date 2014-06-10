<?php

class Cockpit_Restaurant extends Crunchbutton_Restaurant {

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
			$q = '
				select * from `order`
				where id_restaurant="'.$this->id_restaurant.'"
				and DATE(`date`) >= "' . (new DateTime($filters['start']))->format('Y-m-d') . '"
				and DATE(`date`) <= "' . (new DateTime($filters['end']))->format('Y-m-d') . '"
				and name not like "%test%"
				order by `pay_type` asc, `date` asc ';
			$orders = Order::q($q);
			$this->_payableOrders = $orders;
		}
		return $this->_payableOrders;
	}

}