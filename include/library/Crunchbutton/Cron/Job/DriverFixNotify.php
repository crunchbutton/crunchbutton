<?php

class Crunchbutton_Cron_Job_DriverFixNotify extends Crunchbutton_Cron_Log {

	public function run(){

		$q = '
		select `order`.* from `order`
		left join restaurant using (id_restaurant)
		left join admin_notification_log using (id_order)
		where restaurant.delivery_service=1 and restaurant.active=1 and admin_notification_log.id_admin_notification_log is null
		and `order`.date > date_sub(now(), interval 10 minute)
		order by `order`.id_order desc
		';
		$orders = Order::q($q);

		foreach ($orders as $order) {
			echo 'sending notifications for order '.$order->id_order."\n";
			$order->notifyDrivers();
		}

		echo 'done notifying drivers';

		// it always must call finished method at the end
		$this->finished();
	}
}