<?php

class Crunchbutton_Cron_Job_DriverFixNotify extends Crunchbutton_Cron_Log {

	public function run(){

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
			if( $order->wasAcceptedByRep() || $order->wasCanceled() ) {
				continue;
			}

			$message = "It seems drivers haven't receive notifications for the order #{$order->id_order}. Please re-send it!";

			$ticket = Crunchbutton_Support::createNewWarning( [ 'id_order' => $order->id_order,
																													'body' => $message,
																													'bubble' => true ] );

			$body = 'Monitor: Issue #6767<br><br>';
			$body .= "CS ticket #{$ticket->id_support}<br><br>";
			$body .= $message;

			$params = array( 'to' => 'dev@crunchbutton.com', 'message' => $body );
			$params[ 'subject' ] = 'CB DEV - Monitor ' . date( 'm/d/Y H:i:s' );
			$email = new Crunchbutton_Email_Dev( $params );
			$email->send();
		}

		$this->finished();
	}
}

