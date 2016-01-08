<?php

class Crunchbutton_Queue_Order extends Crunchbutton_Queue {

	public function run() {

		$debug_dt = new DateTime('now', new DateTimeZone(c::config()->timezone));
		$debugDtString0 = $debug_dt->format('Y-m-d H:i:s');
		$preordered = $this->order()->preordered;
		if( !$preordered ){
			// send customer a receipt in 30 seconds
			$q = Queue::create([
				'type' => Crunchbutton_Queue::TYPE_ORDER_RECEIPT,
				'id_order' => $this->order()->id_order,
				'seconds' => 30
			]);
		}

		// send restaurants notifications
		$this->order()->notifyRestaurants();

		if ($this->order()->restaurant()->delivery_service){

			$debug_dt = new DateTime('now', new DateTimeZone(c::config()->timezone));
			$debugDtString1 = $debug_dt->format('Y-m-d H:i:s');

			// get active community drivers
			$drivers = $this->order()->getDriversToNotify();

			$debug_dt = new DateTime('now', new DateTimeZone(c::config()->timezone));
			$debugDtString2 = $debug_dt->format('Y-m-d H:i:s');

     	$dl = $this->order()->community()->delivery_logistics;
			// perform delivery logistics only if there are multiple drivers and it is enabled, and
			//  it is not a pre-order
			if (!$preordered && $dl && $drivers->count() > 1) {
				if ($dl == Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX) {
					Log::debug(['id_order' => $this->order()->id_order, 'time' => $debugDtString0, 'stage' => 'start_queue_run',
						'type' => 'complexLogistics']);
					Log::debug(['id_order' => $this->order()->id_order, 'time' => $debugDtString1, 'stage' => 'before_get_drivers',
						'type' => 'complexLogistics']);
					Log::debug(['id_order' => $this->order()->id_order, 'time' => $debugDtString2, 'stage' => 'after_get_drivers',
						'type' => 'complexLogistics']);
				}
				$l = new Crunchbutton_Order_Logistics($dl, $this->order(), $drivers);
				$l->process();

				// TODO: Add logic here to check for current minimum ETA
                // TODO: If ETA is too large, notify customer service

				$numActiveDriversWithPriority = $l->numDriversWithPriority - $l->numInactiveDriversWithPriority;
				if ($l->numDriversWithPriority < 0) {
					// Something went wrong.  Just notify all drivers
					$this->order()->notifyDrivers();
				} else if (($l->numDriversWithPriority == 1) || ($numActiveDriversWithPriority == 1)){
					$retval = $this->order()->checkBeforeNotifications($drivers);
					if (!is_null($retval)) {
						foreach ($l->drivers() as $driver) {
							$hasUnexpired = Crunchbutton_Admin_Notification_Log::adminHasUnexpiredNotification($this->order()->id_order, $driver->id_admin);
							// This is in case things run late and there is a notification already
							if (!$hasUnexpired) {
								Log::debug(['id_order' => $this->order()->id_order, 'id_admin' => $driver->id_admin, 'stage' => 'Does not have unexpired notification',
									'type' => 'logistics']);
								$seconds = $driver->__seconds ? intval($driver->__seconds) : 0;
								if ($driver->__priority) {
									// The seconds for the driver with priority should be 0, but I'm leaving the code for setting
									//  seconds as is.
									if ($driver->__isProbablyInactive) {
										$priorityMsgType = Crunchbutton_Admin_Notification::PRIORITY_MSG_INACTIVE_DRIVER_PRIORITY;
									} else if ($l->numInactiveDriversWithPriority >= 1){
										// Second place, active driver
										$priorityMsgType = Crunchbutton_Admin_Notification::PRIORITY_MSG_SECOND_PLACE_DRIVER_PRIORITY;
									} else{
										$priorityMsgType = Crunchbutton_Admin_Notification::PRIORITY_MSG_PRIORITY;
									}

									$q = Queue::create([
										'type' => Crunchbutton_Queue::TYPE_NOTIFICATION_DRIVER_PRIORITY,
										'id_order' => $this->order()->id_order,
										'id_admin' => $driver->id_admin,
										'seconds' => $seconds,
										'info' => $priorityMsgType
									]);
								} else {
									$q = Queue::create([
										'type' => Crunchbutton_Queue::TYPE_NOTIFICATION_DRIVER,
										'id_order' => $this->order()->id_order,
										'id_admin' => $driver->id_admin,
										'seconds' => $seconds
									]);
								}
								$totalSeconds = $seconds + Crunchbutton_Admin_Notification::FIRST_DELAY;
								$this->order()->registerAfterNotifications($driver->id_admin, $totalSeconds);
							} else{
								Log::debug(['id_order' => $this->order()->id_order, 'id_admin' => $driver->id_admin, 'stage' => 'Has unexpired notification',
									'type' => 'logistics']);

							}

						}

						$this->order()->checkForNoRepsNotifications($retval['needDrivers'], $retval['hasDriversWorking']);

					}
				} else {
					// No special messages if all drivers get the same priority or multiple drivers get
					//  high priority

					// queue notifications for drivers at specific times
					$retval = $this->order()->checkBeforeNotifications($drivers);
					if (!is_null($retval)) {
						foreach ($l->drivers() as $driver) {
							$hasUnexpired = Crunchbutton_Admin_Notification_Log::adminHasUnexpiredNotification($this->order()->id_order, $driver->id_admin);
							if (!$hasUnexpired) {
								$seconds = $driver->__seconds ? intval($driver->__seconds) : 0;
								$q = Queue::create([
									'type' => Crunchbutton_Queue::TYPE_NOTIFICATION_DRIVER,
									'id_order' => $this->order()->id_order,
									'id_admin' => $driver->id_admin,
									'seconds' => $seconds
								]);
								$totalSeconds = $seconds + Crunchbutton_Admin_Notification::FIRST_DELAY;
								$this->order()->registerAfterNotifications($driver->id_admin, $totalSeconds);
							}
						}
						$this->order()->checkForNoRepsNotifications($retval['needDrivers'], $retval['hasDriversWorking']);

					}
				}
			} else {
				$this->order()->notifyDrivers();
			}
		}

		// Send non-scheduled community drivers orders. #7281
		$community = $this->order()->community();
		if( $community && $community->notify_non_shift_drivers && $community->notify_non_shift_drivers_min ){

			if( $this->order()->preordered ){
				$seconds = 45 * 60;
			} else {
				$seconds = $community->notify_non_shift_drivers_min * 60;
			}

			$q = Queue::create([
				'type' => Crunchbutton_Queue::TYPE_NOTIFICATION_DRIVER_HELPOUT,
				'id_order' => $this->order()->id_order,
				'seconds' => $seconds
			]);
		}

		// replaces Crunchbutton_Cron_Job_OrderRules
		$rules = new Crunchbutton_Order_Rules;
		$rules->run($this->order());

		return self::STATUS_SUCCESS;
	}
}
