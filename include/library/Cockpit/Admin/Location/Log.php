<?php

class Cockpit_Admin_Location_Log extends Cana_Table {

	public function save($newItem = 0){
		(new Admin_Location([
			'id_admin' => $this->id_admin,
			'date' => $this->date,
			'lat' => $this->lat,
			'lon' => $this->lon,
			'accuracy' => $this->accuracy
		]))->preSave();
		parent::save();
	}

	public function __construct($id = null) {
		parent::__construct(c::logDB());
		self::dbWrite(c::logDB());

		$this
			->table('admin_location_log')
			->idVar('id_admin_location_log')
			->load($id);
	}
}
