<?php

class Controller_Api_Test_Sandbox extends Cana_Controller {
	public function init(){

		Crunchbutton_Cron_Job_CSTicketsDigest::run();

	}
}