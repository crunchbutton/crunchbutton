<?php

class Crunchbutton_Queue_Order_Receipt extends Crunchbutton_Queue {
	public function run() {
		// send customer a receipt
		$order->receipt();
		
		return self::STATUS_SUCCESS;
	}
}