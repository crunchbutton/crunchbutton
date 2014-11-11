<?php

class Cockpit_Payment_Schedule extends Cana_Table {

	const TYPE_RESTAURANT = 'restaurant';
	const TYPE_DRIVER = 'driver';

	const PAY_TYPE_PAYMENT = 'payment';
	const PAY_TYPE_REIMBURSEMENT = 'reimbursement';

	const STATUS_SCHEDULED = 'scheduled';
	const STATUS_PROCESSING = 'processing';
	const STATUS_DONE = 'done';
	const STATUS_ERROR = 'error';

	public function __construct($id = null) {
		parent::__construct();
		$this->table('payment_schedule')->idVar('id_payment_schedule')->load($id);
	}

	public function lastRestaurantStatusDate(){
		$query = "SELECT MAX( DATE_FORMAT( date, '%m/%d/%Y' ) ) AS date FROM payment_schedule WHERE id_restaurant IS NOT NULL";
		$result = c::db()->get( $query );
		return $result->_items[0]->date;
	}


	public function checkBalancedStatus(){
		$payment = $this->payment();
		if( $payment->id_payment ){
			return $payment->checkBalancedStatus();
		}
		return false;
	}

	public function lastDriverStatusDate(){
		$query = "SELECT MAX( DATE_FORMAT( date, '%m/%d/%Y' ) ) AS date FROM payment_schedule WHERE id_driver IS NOT NULL";
		$result = c::db()->get( $query );
		return $result->_items[0]->date;
	}

	public function exports(){
		$out = $this->properties();
		foreach ( $out as $key => $value ) {
			if( is_null( $value ) ){
				unset( $out[ $key ] );
			}
		}
		if( $out[ 'pay_type' ] == 'reimbursement' ){
			$out[ 'title' ] = 'Reimbursement';
			$out[ 'pay_type' ] = Cockpit_Payment_Schedule::PAY_TYPE_REIMBURSEMENT;
		} else {
			$out[ 'title' ] = 'Payment';
			$out[ 'pay_type' ] = Cockpit_Payment_Schedule::PAY_TYPE_PAYMENT;
		}
		return $out;
	}

	public function payment(){
		if( $this->id_payment ){
			return Crunchbutton_Payment::o( $this->id_payment );
		}
	}

	public function admin() {
		return Admin::o( $this->id_admin );
	}

	public function driver() {
		return Admin::o( $this->id_driver );
	}

	public function restaurant() {
		return Restaurant::o( $this->id_restaurant );
	}

	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime($this->date, new DateTimeZone(c::config()->timezone));
			if( $this->id_restaurant ){
				$this->_date->setTimezone(new DateTimeZone($this->restaurant()->timezone));
			} else if( $this->id_driver ){
				$this->_date->setTimezone(new DateTimeZone($this->driver()->timezone));
			}
		}
		return $this->_date;
	}

	public function status_date() {
		if (!isset($this->_status_date)) {
			$this->_status_date = new DateTime($this->status_date, new DateTimeZone(c::config()->timezone));
			if( $this->id_restaurant ){
				$this->_status_date->setTimezone(new DateTimeZone($this->restaurant()->timezone));
			} else if( $this->id_driver ){
				$this->_status_date->setTimezone(new DateTimeZone($this->driver()->timezone));
			}
		}
		return $this->_status_date;
	}

	public function statusToDriver( $schedule ){
		$out = [];
		if( $schedule->status == Cockpit_Payment_Schedule::STATUS_DONE && $schedule->id_payment ){
			$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
			$expected = $schedule->payment()->date();
			$out[ 'range_date' ] = $schedule->range_date;
			$out[ 'send_date' ] = ( string ) $expected->format( 'M jS Y' );
			$expected->modify( '+3 Weekday' );
			$out[ 'paid_date' ] = ( string ) $expected->format( 'M jS Y' );
			if( $now->format( 'Ymd' ) >= $expected->format( 'Ymd' ) ){
				$out[ 'status' ] = 'Paid';
			} else {
				$out[ 'status' ] = 'Processing';
			}
		} else {
			$out[ 'status' ] = 'Pending';
			$out[ 'paid_date' ] = '-';
		}
		return $out;
	}

	public function driversSchedulesFromDate( $date ){
		$query = 'SELECT ps.*, a.name AS driver FROM payment_schedule ps
								INNER JOIN admin a ON a.id_admin = ps.id_driver
								WHERE DATE_FORMAT( ps.date, \'%m/%d/%Y\' ) = "' . $date . '" ORDER BY ps.id_payment_schedule DESC';
		return Cockpit_Payment_Schedule::q( $query );
	}


	public function restaurantSchedulesFromDate( $date ){
		$query = 'SELECT ps.*, r.name AS restaurant FROM payment_schedule ps
								INNER JOIN restaurant r ON r.id_restaurant = ps.id_restaurant
								WHERE DATE_FORMAT( ps.date, \'%m/%d/%Y\' ) = "' . $date . '" ORDER BY ps.id_payment_schedule DESC';
		return Cockpit_Payment_Schedule::q( $query );
	}

	public function restaurantNotCompletedSchedules(){
		$query = 'SELECT ps.*, r.name AS restaurant FROM payment_schedule ps
								INNER JOIN restaurant r ON r.id_restaurant = ps.id_restaurant
								WHERE ps.status != "' . Cockpit_Payment_Schedule::STATUS_DONE . '" ORDER BY ps.id_payment_schedule DESC';
		return Cockpit_Payment_Schedule::q( $query );
	}

	public function driverPaymentByIdAdmin( $id_driver, $limit = 10 ){
		if( $limit === '*' ){
			$limit = '';
		} else {
			$limit = ' LIMIT ' . $limit;
		}
		$query = 'SELECT * FROM payment_schedule WHERE id_driver = "' . $id_driver . '" ORDER BY id_payment_schedule DESC ' . $limit;
		return Cockpit_Payment_Schedule::q( $query );
	}

	public function driverNotCompletedSchedules(){
		$query = 'SELECT ps.*, a.name AS driver FROM payment_schedule ps
								INNER JOIN admin a ON a.id_admin = ps.id_driver
								WHERE ps.status != "' . Cockpit_Payment_Schedule::STATUS_DONE . '" ORDER BY ps.id_payment_schedule DESC';
		return Cockpit_Payment_Schedule::q( $query );
	}

	public function total_orders(){
		$result = c::db()->get( 'SELECT COUNT(*) total FROM payment_schedule_order WHERE id_payment_schedule = "' . $this->id_payment_schedule . '" ORDER BY id_order DESC' );
		return $result->_items[0]->total;
	}

	public function orders(){
		return Cockpit_Payment_Schedule_Order::q( 'SELECT * FROM payment_schedule_order WHERE id_payment_schedule = "' . $this->id_payment_schedule . '" ORDER BY id_order DESC' );
	}

	public function referrals(){
		return Cockpit_Payment_Schedule_Referral::q( 'SELECT * FROM payment_schedule_referral WHERE id_payment_schedule = "' . $this->id_payment_schedule . '" ORDER BY id_referral DESC' );
	}

	public function invites(){
		return $this->referrals();
	}

	public function shifts(){
		return Cockpit_Payment_Schedule_Shift::q( 'SELECT * FROM payment_schedule_shift WHERE id_payment_schedule = "' . $this->id_payment_schedule . '" ORDER BY id_admin_shift_assign DESC' );
	}

}