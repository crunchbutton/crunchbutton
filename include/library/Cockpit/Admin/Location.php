<?php

class Cockpit_Admin_Location extends Cana_Table {
	public function exports() {
		$exports = [
			'lat' => $this->lat,
			'lon' => $this->lon,
			'accuracy' => $this->accuracy,
			'date' => $this->date
		];
		return $exports;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('admin_location')
			->idVar('id_admin_location')
			->load($id);
	}
}