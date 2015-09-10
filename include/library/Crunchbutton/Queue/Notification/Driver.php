<?php

class Crunchbutton_Queue_Notification_Driver extends Crunchbutton_Queue {
	public function run() {
		$status = $this->order()->status()->last();
		
		// dont send a notification if its already accepted
		if ($status['status'] != 'new') {
			return self::STATUS_STOPPED;
		}
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

		// notify driver of order
		foreach($this->driver()->activeNotifications() as $notification) {
			$notification->send($this->order());
			Log::debug([
				'order' => $this->order()->id_order,
				'action' =>  '#'.$this->order()->id_order.' sending ** QUEUE ** notification to ' . $this->driver()->name . ' # ' . $notification->value,
				'type' => 'delivery-driver', 'hostname' => $hostname, 'pid' => $pid, 'ppid' => $ppid
			]);
		}

		return self::STATUS_SUCCESS;
	}
}