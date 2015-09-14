<?php

class Crunchbutton_Cron_Job_DriverFixNotify extends Crunchbutton_Cron_Log {

	public function run(){

		$hostname = gethostname();
		$pid = getmypid();
		$ppid = NULL;
//			$ppid = posix_getppid();
		if (is_null($hostname)) {
			$hostname = "NA";
		}
		if (is_null($pid)) {
			$pid = "NA";
		}
		if (is_null($ppid)) {
			$ppid = "NA";
		}

		$q = '
		select `order`.* from `order`
		left join restaurant using (id_restaurant)
		left join admin_notification_log using (id_order)
		where restaurant.delivery_service=1 and restaurant.active=true and admin_notification_log.id_admin_notification_log is null
		and `order`.date > date_sub(now(), interval 10 minute) and `order`.date < date_sub(now(), interval 1 minute)
		order by `order`.id_order desc
		';
		$orders = Order::q($q);

		foreach ($orders as $order) {
			echo 'sending notifications for order '.$order->id_order."\n";
			$id_order = $order->id_order;
			Log::debug(['order' => $id_order, 'action' => "Run cron job DriverFixNotify", 'type' => 'delivery-driver',
				'hostname' => $hostname, 'pid' => $pid, 'ppid' => $ppid]);
			$order->notifyDrivers();
		}

		echo 'done notifying drivers';
		Log::debug(['action' => "Run cron job DriverFixNotify finished", 'type' => 'delivery-driver',
			'hostname' => $hostname, 'pid' => $pid, 'ppid' => $ppid]);
		// it always must call finished method at the end
		$this->finished();
	}
}

