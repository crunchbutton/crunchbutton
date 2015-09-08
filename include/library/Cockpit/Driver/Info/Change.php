<?php

class Crunchbutton_Driver_Info_Change extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('driver_info_change')
			->idVar('id_driver_info_change')
			->load($id);
	}
}