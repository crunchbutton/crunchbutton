<?php

class Crunchbutton_Queue_Notification_Driver_Priority extends Crunchbutton_Queue {

	public function run() {
		$status = $this->order()->status()->last();

		// dont send a notification if its already accepted
		if ($status['status'] != 'new') {
			return self::STATUS_STOPPED;
		}

		$driver = $this->driver();
		$order = $this->order();
		// Use straight text instead of json for now, for efficiency
		$priorityMsgType = $this->info;
		if (is_null($priorityMsgType)) {
			$priorityMsgType = 0;
		} else{
			$priorityMsgType = intval($priorityMsgType);
		}
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
				$notification->sendPriority($order, $priorityMsgType);
				Log::debug([
					'order' => $order->id_order,
					'action' =>  '#'.$order->id_order.' sending ** QUEUE ** priority notification to ' . $driver->name . ' # ' . $notification->value,
					'type' => 'delivery-driver', 'hostname' => $hostname, 'pid' => $pid, 'ppid' => $ppid, 'priorityMsgType' => $priorityMsgType
				]);
			}
		}

		return self::STATUS_SUCCESS;
	}
}
