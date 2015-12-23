<?php

class Crunchbutton_Queue_Notification_Driver_HelpOut extends Crunchbutton_Queue {

	public function run() {

		$order = $this->order();
		if( $order->wasAcceptedByRep() || $order->wasCanceled() ) {
			return self::STATUS_SUCCESS;
		}

		$community = $order->community();
		if( $community->notify_non_shift_drivers )
		$list = $community->driversHelpOut();
		if( count( $list ) ){
			foreach( $list as $driver ){
				if( $driver->couldReceiveHelpOutNotification() ){
					foreach ($driver->activeNotifications() as $adminNotification) {
						$adminNotification->sendHelpOut( $order );
						$message = '#' . $order->id_order . ' sending driver help out notification to ' . $driver->name . ' #' . $adminNotification->value;
						Log::debug(['order' => $order->id_order, 'action' => $message, 'type' => 'delivery-driver']);
						echo $message . "\n";
					}
				}
			}
		}
		return self::STATUS_SUCCESS;
	}
}
