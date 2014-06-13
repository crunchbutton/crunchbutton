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

	public function orders() {
		return Cockpit_Payment_Schedule_Order::q( 'SELECT * FROM payment_schedule_order WHERE id_payment_schedule = "' . $this->id_payment_schedule . '"' );
	}

}