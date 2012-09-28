<?php

class Crunchbutton_Notification_Log extends Cana_Table {
	public function order() {
		return Order::o($this->id_order);
	}
	
	public function tries() {
		return self::q('select * from notification_log where id_order="'.$this->id_order.'"')->count();
	}
	
	public function notification() {
		return Notification::o($this->id_notification);
	}
	
	public function callback() {
		$nl = Notification_Log::q('select * from notification_log where id_order="'.$this->id_order.'" and status="callback"');

		if ($nl->count() >= c::config()->twilio->maxcallback) {
			$this->status = 'maxcallbackexceeded';
			$this->save();
		} else {
			$this->queCallback();
		}
	}
	
	
	public function queCallback() {
//		exec(c::config()->dirs->root.'cli/callback.php '.$this->id_notification.' '.$this->id_order.' 2>&1', $o);
//		print_r($o);
		exec('nohup '.c::config()->dirs->root.'cli/callback.php '.$this->id_notification.' > /dev/null 2>&1 &');
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('notification_log')
			->idVar('id_notification_log')
			->load($id);
	}
}