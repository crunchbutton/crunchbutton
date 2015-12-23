<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init(){
		Crunchbutton_Queue::process();
		// Crunchbutton_Cron_Log::test();
	}
}
