<?php

class Crunchbutton_Queue_Order extends Crunchbutton_Queue {
	public function run() {
		
		// send hipchat notification
		// @pererinha is this still needed?
		//Crunchbutton_Hipchat_Notification::OrderPlaced($this->order());
		
		// run restaurant specific rules
		// @pererinha there is also a cron that does this for all orders. can we remove Crunchbutton_Cron_Job_OrderRules and just put that here instead?
		if ($this->order()->type == Crunchbutton_Order::PROCESS_TYPE_RESTAURANT) {
			$rules = new Crunchbutton_Order_Rules;
			$rules->run($this->order());
		}
		
		// send customer a receipt in 30 seconds
		$q = Queue::create([
			'type' => 'order-receipt',
			'id_order' => $this->id_order,
			'seconds' => 30
		]);
		
		
		// notify people
		$order->notify();
		
		return self::STATUS_SUCCESS;
	}
}