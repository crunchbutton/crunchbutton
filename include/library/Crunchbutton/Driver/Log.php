<?php

class Crunchbutton_Driver_Log extends Cana_Table {

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('driver_log')
			->idVar('id_driver_log')
			->load($id);
	}

	public function byDriver( $id_admin ){
		if( $id_admin ){
			$logs = Crunchbutton_Driver_Log::q( 'SELECT * FROM driver_log WHERE id_admin = "' . $id_admin . '"' );
			
		} else {
			return [];
		}
		
	}

	public function exports(){
		return $this->properties();
	}
}