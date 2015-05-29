<?php

class Crunchbutton_Queue_Notification_Minutes_Way extends Crunchbutton_Queue {

	public function run() {

		$this->order()->textCustomer( Cockpit_Order::I_AM_5_MINUTES_AWAY );

		return self::STATUS_SUCCESS;
	}
}