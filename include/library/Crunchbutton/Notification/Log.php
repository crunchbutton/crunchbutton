<?php

class Crunchbutton_Notification_Log extends Cana_Table {
	public function order() {
		return Order::o($this->id_order);
	}
	
	public function tries() {
		return self::q('select * from notification_log where id_order="'.$this->id_order.'"')->count();
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('notification_log')
			->idVar('id_notification_log')
			->load($id);
	}
}