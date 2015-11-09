<?php

class Cockpit_Admin_Location_Requested extends Cana_Table {

	const STATUS_DENIED = 'denied';
	const STATUS_PERMITTED = 'permitted';

	public function admin() {
		return Admin::o($this->id_admin);
	}

	public static function lastStatus( $id_admin ){
		return Cockpit_Admin_Location_Requested::q( 'SELECT * FROM admin_location_requested WHERE id_admin = ? ORDER BY id_admin_location_requested DESC LIMIT 1', [ $id_admin ] )->get( 0 );
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('admin_location_requested')
			->idVar('id_admin_location_requested')
			->load($id);
	}
}