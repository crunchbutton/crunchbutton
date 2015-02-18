<?php

class Crunchbutton_Admin_Change extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('admin_change')
			->idVar('id_admin_change')
			->load($id);
	}
}