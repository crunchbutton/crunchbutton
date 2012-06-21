<?php

class Crunchbutton_Company extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('company')
			->idVar('id_company')
			->load($id);
	}
}