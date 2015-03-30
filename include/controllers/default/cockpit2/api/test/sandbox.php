<?php

class Controller_Api_Test_Sandbox extends Cana_Controller {
	public function init(){

		Cockpit_Community_Closed_Log::processLog();

	}
}