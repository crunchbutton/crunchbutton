<?php

class Controller_Api_Test_Sandbox extends Crunchbutton_Controller_Account {
	public function init() {
		$monitor = new Crunchbutton_Log_Monitor;
		$monitor->run();
	}
}