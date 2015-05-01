<?php

class Controller_Api_Test_Sandbox extends Crunchbutton_Controller_Account {

	public function init() {

		$cron = new Crunchbutton_Cron_Job_CheckPexCardApiStatus;
		$cron->run( [] );

	}
}