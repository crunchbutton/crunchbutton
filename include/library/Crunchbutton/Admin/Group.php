<?php

class Crunchbutton_Admin_Group extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('admin_group')
			->idVar('id_admin_group')
			->load($id);
	}
}