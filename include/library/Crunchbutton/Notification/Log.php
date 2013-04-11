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
		$nl = Notification_Log::q('select * from notification_log where id_order="'.$this->id_order.'" and status="callback" and `type`="twilio"');

		if ($nl->count() >= c::config()->twilio->maxcallback) {
			$this->status = 'maxcallbackexceeded';
			$this->save();
			
			if (c::env() != 'live') {
				return;
			}

			Log::critical([
				'id_order' => $this->id_order, 
				'id_notification_log' => $this->id_notification_log,
				'id_notification' => $this->id_notification,
				'id_restaurant' => $this->order()->restaurant()->id_restaurant,
				'restaurant' => $this->order()->restaurant()->name,
				'restaurant_phone' => $this->order()->restaurant()->phone,
				'customer_phone' => $this->order()->phone,
				'customer_name' => $this->order()->name,
				'action' => '#'.$this->id_order.' MAX CB for '.$this->order()->restaurant()->name."\nR# ".$this->order()->restaurant()->phone()."\n C# ".$this->order()->phone(),
				'host' => c::config()->host_callback,
				'type' => 'notification'
			]);

		} else {
			$this->queCallback();
		}
	}
	
	public function queCallback() {
		$log = $this;

		c::timeout(function() use($log) {
			$not = $log->notification();
			$order = $log->order();
			$not->send($order);
		}, c::config()->twilio->callbackTime);		
	}
	
	public function confirm() {
		$nl = Notification_Log::q('select * from notification_log where id_order="'.$this->id_order.'" and status="callback" and `type`="confirm"');

		if ($nl->count() >= c::config()->twilio->maxconfirmback) {
			$this->status = 'maxconfirmbackexceeded';
			$this->save();
			
			if (c::env() != 'live') {
				return;
			}

			Log::critical([
				'id_order' => $this->id_order, 
				'id_notification_log' => $this->id_notification_log,
				'id_notification' => $this->id_notification,
				'id_restaurant' => $this->order()->restaurant()->id_restaurant,
				'restaurant' => $this->order()->restaurant()->name,
				'restaurant_phone' => $this->order()->restaurant()->phone,
				'customer_phone' => $this->order()->phone,
				'customer_name' => $this->order()->name,
				'action' => '#'.$this->id_order.' MAX CONFIRM CB for '.$this->order()->restaurant()->name."\nR# ".$this->order()->restaurant()->phone()."\nC# ".$this->order()->phone(),
				'host' => c::config()->host_callback,
				'type' => 'notification'
			]);
		} else {

			$this->order()->queConfirm();
		}
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('notification_log')
			->idVar('id_notification_log')
			->load($id);
	}
}