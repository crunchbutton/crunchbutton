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
			'id_order' => $this->order()->id_order,
			'seconds' => 30
		]);
		
		// send restaurants notifications
		$this->order()->notifyRestaurants();
		
		// get active community drivers
		$drivers = $this->order()->getDriversToNotify();
		
		// perform delivery logistics only if there are multiple drivers and it is enabled
		if ($this->order()->community()->delivery_logistics && $drivers->count() > 1) {
			$l = new Order_Logistics($this->order());

			// queue notifications for drivers at specific times
			foreach ($l->drivers() as $driver) {
				$q = Queue::create([
					'type' => 'notification-driver',
					'id_order' => $this->order()->id_order,
					'id_admin' => $driver->id_admin,
					'seconds' => $driver->seconds
				]);
			}
		} else {
			$this->order()->notifyDrivers();
		}
		
		return self::STATUS_SUCCESS;
	}
}