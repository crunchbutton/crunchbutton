<?php

class Controller_api_temp_sandbox extends Crunchbutton_Controller_Account {

	public function init() {


		$cron = new Crunchbutton_Cron_Job_VerifyDriverAccount;
		$cron->run();
		die( "hard" );

	}
}
