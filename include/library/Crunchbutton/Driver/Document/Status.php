<?php

class Crunchbutton_Driver_Document_Status extends Cana_Table {

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('driver_document')
			->idVar('id_driver_document')
			->load($id);
	}

	public function document( $id_admin, $id_driver_document ){
		return Crunchbutton_Driver_Document_Status::q( 'SELECT * FROM driver_document_status WHERE id_admin = ' . $id_admin . ' AND id_driver_document =' . $id_driver_document )->get( 0 );	
	}

	public function exports(){
		return $this->properties();
	}

}