<?php

class Crunchbutton_Hour_Change extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('hour_change')
			->idVar('id_hour_change')
			->load($id);
	}
}