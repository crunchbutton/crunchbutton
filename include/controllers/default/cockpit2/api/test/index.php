<?php

class Controller_api_test extends Cana_Controller {

	public function init(){

		$cron = new Crunchbutton_Cron_Job_AutoShutDownCommunity;
		$cron->run();

	}

}