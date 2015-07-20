<?php

class Crunchbutton_Queue_Notification_Driver_Priority extends Crunchbutton_Queue {

	public function run() {

		$driver = $this->driver();
		$order = $this->order();
		if( $order->id_order && $driver->id_admin ){
			$notifications = $driver->activeNotifications();
			foreach( $notifications as $notification ){
				$notification->sendPriority( $order );
			}
		}

		return self::STATUS_SUCCESS;
	}
}
