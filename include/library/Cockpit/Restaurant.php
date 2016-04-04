<?php

class Cockpit_Restaurant extends Crunchbutton_Restaurant {

	public function __construct($id = null) {
		$this->_changeSetName = 'Crunchbutton_Restaurant';
		$this->changeOptions([
			'created' => true
		]);

		parent::__construct($id);
	}

	// Settlement stuff

	// get the last payment
	public function getLastPayment(){
		if (!isset($this->_lastPayment)) {
			$this->_lastPayment = Payment::q('select * from payment where id_restaurant=? order by date desc limit 1', [$this->id_restaurant])->get(0);
		}
		return $this->_lastPayment;
	}

	// get the last sent payment
	public function sendPayment($filters = []) {
		if (!isset($this->_lastPayment)) {
			$this->_lastPayment = Payment::q('select * from payment where id_restaurant=? order by date desc limit 1', [$this->id_restaurant])->get(0);
		}
		return $this->_lastPayment;
	}

	// get number of orders of a given status for last 7 days
	public function numberOfOrdersByStatus( $status ){
		$keys = [];
		$interval = 'AND o.date BETWEEN NOW() - INTERVAL 7 DAY AND NOW()';
		// orders with no status
		if( !$status ){
			$query = '
				SELECT COUNT(*) AS total
				FROM `order` o
				WHERE o.id_restaurant = ?
					' . $interval . '
					AND o.id_order NOT IN(	SELECT DISTINCT(o.id_order) id
						FROM `order` o
						INNER JOIN order_action oa ON oa.id_order = o.id_order
						WHERE o.id_restaurant = ? ' . $interval . ' )';
			$keys[] = $this->id_restaurant;
			$keys[] = $this->id_restaurant;

		} else {
			$query = 'SELECT COUNT(*) AS total
								FROM `order` o
								INNER JOIN order_action oa ON oa.id_order = o.id_order
								AND oa.type = ?
								INNER JOIN
								  (SELECT MAX(oa.id_order_action) AS id_order_action,
								          type,
								          id_order
								   FROM order_action oa
								   GROUP BY oa.id_order
								   ORDER BY oa.id_order_action) actions ON actions.id_order = o.id_order
								AND actions.id_order_action = oa.id_order_action
								WHERE o.id_restaurant = ? ' . $interval;

			$keys[] = $status;
			$keys[] = $this->id_restaurant;
		}
		$data = c::db()->get( $query, $keys)->get( 0 );
		return $data->total;
	}

	// get orders that are payable; not test, within our date range, it just return the order, the calc are made at settlement class
	public function payableOrders($filters = []) {

		if (!isset($this->_payableOrders)) {
			$q = 'SELECT * FROM `order`
							WHERE id_restaurant=?
								AND date >= ?
								AND date <= ?
								AND NAME NOT LIKE \'%test%\'
							ORDER BY `pay_type` ASC, `date` ASC ';
			$start = (new DateTime($filters['start']))->format('Y-m-d') . ' 00:00:00' ;
			$end = (new DateTime($filters['end']))->format('Y-m-d') . ' 23:59:59' ;
			$orders = Order::q($q, [$this->id_restaurant, $start, $end]);
			$this->_payableOrders = $orders;
		}
		return $this->_payableOrders;
	}

	public function chain(){
		if( !$this->_chain ){
			$chain = Restaurant_Chain::q( 'SELECT * FROM restaurant_chain WHERE id_restaurant = ?', [ $this->id_restaurant ] );
			if( $chain->id_restaurant_chain ){
				return $chain;
			}
		}
		return $this->_chain;
	}

	public function exports($ignore = [], $where = []) {
		$out = parent::exports($ignore, $where);
		$out['images'] = $this->getImages('name');
		$out['payment_type'] = $this->payment_type()->exports();
		if(!$this->open_for_business && $this->reopen_for_business_at){
			$out['closed_for_today'] = true;
		}
		return $out;
	}

	public static function reopenRestaurantsForBusiness(){
		$query = 'SELECT * FROM restaurant WHERE open_for_business = false AND reopen_for_business_at IS NOT NULL AND reopen_for_business_at > ?';
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$restaurants = Restaurant::q( $query, [ $now->format( 'Y-m-d H:i:s' ) ] );
		if( count( $restaurants ) ){
			foreach( $restaurants as $restaurant ){
				$restaurant->reopenForBusiness();
			}
		}
	}

	public function closeForBusinessForToday(){
		$this->open_for_business = false;
		$now = new DateTime( 'now', new DateTimeZone( $this->timezone ) );
		$now->setTime( 23,59, 59 );
		$now->setTimeZone( new DateTimeZone( c::config()->timezone ) );
		$this->reopen_for_business_at = $now->format( 'Y-m-d H:i:s' );
		$this->save();
	}

	public function forceReopenForBusiness(){
		$this->open_for_business = true;
		$this->reopen_for_business_at = null;
		$this->save();
	}

	public function reopenForBusiness(){
		if(!$this->open_for_business && $this->reopen_for_business_at){
			$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
			$reopen_for_business_at = new DateTime( $this->reopen_for_business_at, new DateTimeZone( c::config()->timezone ) );
			if( $now->format( 'YmdHis' ) >= $this->reopen_for_business_at->format( 'YmdHis' ) ){
				$this->open_for_business = true;
				$this->reopen_for_business_at = null;
				$this->save();
			}
		}
	}
}