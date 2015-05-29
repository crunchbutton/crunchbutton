<?php

class Crunchbutton_Report extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('report')
			->idVar('id_report')
			->load($id);
	}
}