<?php

class Cockpit_Admin_Location_Requested extends Cana_Table {
	
	const STATUS_DENIED = 'denied';
	const STATUS_PERMITTED = 'permitted';

	public function admin() {
		return Admin::o($this->id_admin);
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('admin_location_requested')
			->idVar('id_admin_location_requested')
			->load($id);
	}
}