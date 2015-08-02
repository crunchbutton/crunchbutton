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

		if ($this->order()->restaurant()->delivery_service){

			// get active community drivers
			$drivers = $this->order()->getDriversToNotify();

            $dl = $this->order()->community()->delivery_logistics;
			// perform delivery logistics only if there are multiple drivers and it is enabled
			if ($dl && $drivers->count() > 1) {

				$l = new Order_Logistics($dl, $this->order(), $drivers);
                // TODO: Add logic here to check for current minimum ETA
                // TODO: If ETA is too large, notify customer service

				if ($l->numDriversWithPriority < 0) {
					// Something went wrong.  Just notify all drivers
					$this->order()->notifyDrivers();
				} else if ($l->numDriversWithPriority == 1){
					foreach ($l->drivers() as $driver) {
						if ($driver->__priority){
							// The seconds for the driver with priority should be 0, but I'm leaving the code for setting
							//  seconds as is.
							$q = Queue::create([
								'type' => Crunchbutton_Queue::TYPE_NOTIFICATION_DRIVER_PRIORITY,
								'id_order' => $this->order()->id_order,
								'id_admin' => $driver->id_admin,
								'seconds' => $driver->_seconds ? intval($driver->_seconds) : 0
							]);
						} else {
							$q = Queue::create([
								'type' => Crunchbutton_Queue::TYPE_NOTIFICATION_DRIVER,
								'id_order' => $this->order()->id_order,
								'id_admin' => $driver->id_admin,
								'seconds' => $driver->_seconds ? intval($driver->_seconds) : 0
							]);
						}
					}
				} else {
					// No special messages if all drivers get the same priority or multiple drivers get
					//  high priority

					// queue notifications for drivers at specific times
					foreach ($l->drivers() as $driver) {
						$q = Queue::create([
							'type' => Crunchbutton_Queue::TYPE_NOTIFICATION_DRIVER,
							'id_order' => $this->order()->id_order,
							'id_admin' => $driver->id_admin,
							'seconds' => $driver->_seconds ? intval($driver->_seconds) : 0
						]);
					}
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
