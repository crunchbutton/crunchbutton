<?php

class Crunchbutton_Pexcard_Action extends Cana_Table {

	const ACTION_SHIFT_STARTED = 'shift-started';
	const ACTION_SHIFT_FINISHED = 'shift-finished';
	const ACTION_ORDER_ACCEPTED = 'order-accepted';
	const ACTION_ORDER_CANCELLED = 'order-cancelled';
	const ACTION_ARBRITARY = 'arbritary';

	const TYPE_CREDIT = 'credit';
	const TYPE_DEBIT = 'debit';

	public function __construct($id = null) {
		parent::__construct();
		$this->table( 'pexcard_action' )->idVar( 'id_pexcard_action' )->load( $id );
	}

	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime($this->timestamp, new DateTimeZone(c::config()->timezone));
			$this->_date->setTimezone(new DateTimeZone($this->restaurant()->timezone));
		}
		return $this->_date;
	}
}