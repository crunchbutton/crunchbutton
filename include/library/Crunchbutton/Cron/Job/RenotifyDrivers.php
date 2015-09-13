<?php

class Crunchbutton_Cron_Job_RenotifyDrivers extends Crunchbutton_Cron_Log {

	public function run(){
		$hostname = gethostname();
		$pid = getmypid();
		$ppid = NULL;
//			$ppid = posix_getppid();
		if (is_null($hostname)) {
			$hostname = "NA";
		}
		if (is_null($pid)) {
			$pid = "NA";
		}
		if (is_null($ppid)) {
			$ppid = "NA";
		}
		$notification = new Crunchbutton_Admin_Notification();
		$notification->resendNotification();
		Log::debug(['action' => "Run cron job RenotifyDrivers", 'type' => 'delivery-driver',
			'hostname' => $hostname, 'pid' => $pid, 'ppid' => $ppid]);

		// it always must call finished method at the end
		$this->finished();
	}
}