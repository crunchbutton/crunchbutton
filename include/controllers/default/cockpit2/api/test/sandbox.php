<?php

class Controller_Api_Test_Sandbox extends Cana_Controller {
	public function init(){

		$cs = new Crunchbutton_Cron_Job_CSTicketsDigest;
		$cs->run();

	}
}