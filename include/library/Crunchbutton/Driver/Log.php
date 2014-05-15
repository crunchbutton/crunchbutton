<?php

class Crunchbutton_Driver_Log extends Cana_Table {

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('driver_log')
			->idVar('id_driver_log')
			->load($id);
	}

	public function exports(){
		return $this->properties();
	}
}