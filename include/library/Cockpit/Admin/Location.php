<?php

class Cockpit_Admin_Location extends Cana_Table {
	const TIME_LOCATION_VALID = 6 * 60; // seconds

	public function exports() {
		$exports = [
			'lat' => $this->lat,
			'lon' => $this->lon,
			'accuracy' => $this->accuracy,
			'date' => $this->date
		];
		return $exports;
	}
	
	public function valid($seconds = self::TIME_LOCATION_VALID) {
		$date = new DateTime($this->date);
		$now = new DateTime();
		$interval = $date->diff($now);
		$interval->format('%s');

		return $interval > $seconds ? false : true;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('admin_location')
			->idVar('id_admin_location')
			->load($id);
	}
}