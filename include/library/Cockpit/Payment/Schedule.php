<?php

class Cockpit_Payment_Schedule extends Cana_Table {

	const TYPE_RESTAURANT = 'restaurant';
	const TYPE_DRIVER = 'driver';

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

	public function exports(){
		$out = $this->properties();
		foreach ( $out as $key => $value ) {
			if( is_null( $value ) ){
				unset( $out[ $key ] );
			}
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

	public function restaurant() {
		return Restaurant::o( $this->id_restaurant );
	}

	public function restaurantSchedulesFromDate( $date ){
		$query = 'SELECT ps.*, r.name AS restaurant FROM payment_schedule ps
								INNER JOIN restaurant r ON r.id_restaurant = ps.id_restaurant
								WHERE DATE_FORMAT( ps.date, \'%m/%d/%Y\' ) = "' . $date . '" ORDER BY r.name, ps.id_payment_schedule DESC';
		return Cockpit_Payment_Schedule::q( $query );
	}

	public function orders(){
		return Cockpit_Payment_Schedule_Order::q( 'SELECT * FROM payment_schedule_order WHERE id_payment_schedule = "' . $this->id_payment_schedule . '" ORDER BY id_order DESC' );
	}

}