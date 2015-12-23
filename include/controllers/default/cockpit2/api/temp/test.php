<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init(){
<<<<<<< HEAD
		// echo '<pre>';var_dump( 1 );exit();
		$notification = new Crunchbutton_Admin_Notification();
		$notification->notifyNonShiftDrivers();
=======
		Crunchbutton_Queue::process();
		// Crunchbutton_Cron_Log::test();
>>>>>>> master
	}
}
