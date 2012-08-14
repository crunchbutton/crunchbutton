<?php

class Crunchbutton_Hour extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('hour')
			->idVar('id_hour')
			->load($id);
	}
}