<?php

class Crunchbutton_Notification extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('notification')
			->idVar('id_notification')
			->load($id);
	}
}