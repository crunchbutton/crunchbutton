<?php

class Crunchbutton_Queue_Order extends Crunchbutton_Queue {
	public function run() {

		// send customer a receipt in 30 seconds
		$q = Queue::create([
			'type' => Crunchbutton_Queue::TYPE_ORDER_RECEIPT,
			'id_order' => $this->order()->id_order,
			'seconds' => 30
		]);

		// send restaurants notifications
		$this->order()->notifyRestaurants();

		if (intval($this->order()->restaurant()->delivery_service) == 1){

			// get active community drivers
			$drivers = $this->order()->getDriversToNotify();

			// perform delivery logistics only if there are multiple drivers and it is enabled
			if ($this->order()->community()->delivery_logistics && $drivers->count() > 1) {
				$l = new Order_Logistics($this->order());

				// queue notifications for drivers at specific times
				foreach ($l->drivers() as $driver) {
					$q = Queue::create([
						'type' => Crunchbutton_Queue::TYPE_NOTIFICATION_DRIVER,
						'id_order' => $this->order()->id_order,
						'id_admin' => $driver->id_admin,
						'seconds' => $driver->_seconds ? intval($driver->_seconds) : 0
					]);
				}
			} else {
				$this->order()->notifyDrivers();
			}

		}



		// replaces Crunchbutton_Cron_Job_OrderRules
		$rules = new Crunchbutton_Order_Rules;
		$rules->run($this->order());

		return self::STATUS_SUCCESS;
	}
}