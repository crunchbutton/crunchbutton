<?php

class Crunchbutton_Cron_Job_RestaurantFixNotify extends Crunchbutton_Cron_Log {

	public function run(){

		$orders = Order::q( 'SELECT * FROM `order` o
													INNER JOIN (
													SELECT DISTINCT( id_order ) FROM `order` o
													LEFT JOIN notification n ON n.id_restaurant = o.id_restaurant AND n.active = true
													WHERE o.date > date_sub( now(), interval 10 minute ) ) filter ON filter.id_order = o.id_order' );

		echo "start notification send fix";
		echo "<br>\n";

		foreach ($orders as $order) {
			$notifications = $order->restaurant()->notifications();
			foreach( $notifications as $notification ){
				// Check if the notification was sent
				$wasSent = Crunchbutton_Notification_Log::notificationOrder( $order->id_order, $notification->id_notification );
				if( !$wasSent ){
					echo "sending notification " . $notification->id_notification . " for order " . $order->id_order;
					$notification->send( $order );
				} else {
					echo "notification " . $notification->id_notification . " for order " . $order->id_order . " already sent";
				}
				echo "<br>\n";
			}
		}

		// it always must call finished method at the end
		$this->finished();
	}
}