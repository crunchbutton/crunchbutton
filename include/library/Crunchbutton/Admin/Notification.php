<?php

class Crunchbutton_Admin_Notification extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('admin_notification')
			->idVar('id_admin_notification')
			->load($id);
	}
}