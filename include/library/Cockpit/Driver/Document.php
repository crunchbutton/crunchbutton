<?php

class Cockpit_Driver_Document extends Cana_Table {

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('driver_document')
			->idVar('id_driver_document')
			->load($id);
	}

	public function all(){
		return Cockpit_Driver_Document::q( 'SELECT * FROM driver_document ORDER BY `order` ASC' );	
	}

	public function exports(){
		return $this->properties();
	}
}