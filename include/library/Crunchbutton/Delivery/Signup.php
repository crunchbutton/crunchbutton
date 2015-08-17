<?php

class Crunchbutton_Delivery_Signup extends Cana_Table {

	const STATUS_NEW = 'new';
	const STATUS_DELETED = 'deleted';
	const STATUS_ARCHIVED = 'archived';
	const STATUS_REVIEW = 'review';

	public function __construct($id = null) {
		parent::__construct();
		$this->table('delivery_signup')->idVar('id_delivery_signup')->load($id);
	}

	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime($this->date, new DateTimeZone(c::config()->timezone));
			$this->_date->setTimezone(new DateTimeZone( c::config()->timezone ));
		}
		return $this->_date;
	}

	public function save() {
		parent::save();
	}

}