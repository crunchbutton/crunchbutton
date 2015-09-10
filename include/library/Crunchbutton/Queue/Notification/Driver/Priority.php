<?php

class Crunchbutton_Queue_Notification_Driver_Priority extends Crunchbutton_Queue {

	public function run() {

		$driver = $this->driver();
		$order = $this->order();
		if( $order->id_order && $driver->id_admin ){
			$hostname = gethostname();
			$pid = getmypid();
			$ppid = NULL;
//			$ppid = posix_getppid();
			if (is_null($hostname)){
				$hostname="NA";
			}
			if (is_null($pid)){
				$pid="NA";
			}
			if (is_null($ppid)){
				$ppid="NA";
			}
			$notifications = $driver->activeNotifications();
			foreach( $notifications as $notification ){
				$notification->sendPriority( $order );
				Log::debug([
					'order' => $order->id_order,
					'action' =>  '#'.$order->id_order.' sending ** QUEUE ** priority notification to ' . $driver->name . ' # ' . $notification->value,
					'type' => 'delivery-driver', 'hostname' => $hostname, 'pid' => $pid, 'ppid' => $ppid
				]);
			}
		}

		return self::STATUS_SUCCESS;
	}
}
