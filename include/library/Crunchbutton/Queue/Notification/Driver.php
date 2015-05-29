<?php

class Crunchbutton_Queue_Notification_Driver extends Crunchbutton_Queue {
	public function run() {
		$status = $this->order()->status()->last();
		
		// dont send a notification if its already accepted
		if ($status->status != 'new') {
			return self::STATUS_STOPPED;
		}
		
		// notify driver of order
		foreach($this->driver()->activeNotifications() as $notification) {
			$notification->send($this->order());
			Log::debug([
				'order' => $order->id_order,
				'action' =>  '#'.$this->order()->id_order.' sending ** QUEUE ** notification to ' . $this->driver()->name . ' # ' . $notification->value,
				'type' => 'delivery-driver'
			]);
		}

		return self::STATUS_SUCCESS;
	}
}