<?php

class Crunchbutton_Queue_Notification_Your_Driver extends Crunchbutton_Queue {

	public function run() {

		$this->order()->textCustomerAboutDriver();

		return self::STATUS_SUCCESS;
	}
}