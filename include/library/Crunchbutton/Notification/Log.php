<?php

class Crunchbutton_Notification_Log extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('notification_log')
			->idVar('id_notification_log')
			->load($id);
	}
}