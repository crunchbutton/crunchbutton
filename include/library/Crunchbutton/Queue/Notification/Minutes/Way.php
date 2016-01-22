<?php

class Crunchbutton_Queue_Notification_Minutes_Way extends Crunchbutton_Queue {

	public function run() {

		echo "starting 5 min way...\n\n";

		$order = Cockpit_Order::o( $this->id_order, true );

		echo "order:id_order: $order->id_order...\n\n";

		$order->textCustomer( Cockpit_Order::I_AM_5_MINUTES_AWAY );

		echo "finished 5 min way...\n\n";

		return self::STATUS_SUCCESS;
	}
}