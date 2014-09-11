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

	public function hasPaymentType(){
		$payment_type = $this->payment_type();
		if( $payment_type->balanced_id && $payment_type->balanced_bank ){
			return true;
		}
		return false;
	}

	// get the last sent payment
	public function sendPayment($filters = []) {
		if (!isset($this->_lastPayment)) {
			$this->_lastPayment = Payment::q('select * from payment where id_restaurant="'.$this->id_restaurant.'" order by date desc limit 1')->get(0);
		}
		return $this->_lastPayment;
	}

	// get number of orders of a given status for last 7 days
	public function numberOfOrdersByStatus( $status ){
		$interval = 'AND o.date BETWEEN NOW() - INTERVAL 7 DAY AND NOW()';
		// orders with no status
		if( !$status ){
			$query = 'SELECT COUNT(*) AS total
									FROM `order` o
									WHERE o.id_restaurant = "' . $this->id_restaurant . '"
										' . $interval . '
										AND o.id_order NOT IN(	SELECT DISTINCT(o.id_order) id
																							FROM `order` o
																							INNER JOIN order_action oa ON oa.id_order = o.id_order
																							WHERE o.id_restaurant = "' . $this->id_restaurant . '" ' . $interval . ' )';
		} else {
			$query = 'SELECT COUNT(*) AS total
								FROM `order` o
								INNER JOIN order_action oa ON oa.id_order = o.id_order
								AND oa.type = "' . $status . '"
								INNER JOIN
								  (SELECT MAX(oa.id_order_action) AS id_order_action,
								          type,
								          id_order
								   FROM order_action oa
								   GROUP BY oa.id_order
								   ORDER BY oa.id_order_action) actions ON actions.id_order = o.id_order
								AND actions.id_order_action = oa.id_order_action
								WHERE o.id_restaurant = "' . $this->id_restaurant . '" ' . $interval;
		}
		$data = c::db()->get( $query )->get( 0 );
		return $data->total;
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