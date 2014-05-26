<?php

class Crunchbutton_Cron_Job_RenotifyDrivers extends Crunchbutton_Cron_Log {

	public function run(){

		// $notification = new Crunchbutton_Admin_Notification();
		// $notification->resendNotification();

		// it always must call finished method at the end
		$this->finished();
	}
}