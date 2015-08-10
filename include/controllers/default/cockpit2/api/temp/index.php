<?php

class Controller_api_temp extends Crunchbutton_Controller_RestAccount {

	public function init() {
		$job = new Crunchbutton_Cron_Job_<NAME_HERE>;
		$job->run();
	}

}
