<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {
	public function init() {
		Cockpit_Driver_Log::nativeAppLogin();
		Cockpit_Driver_Log::enabledLocation();
		Cockpit_Driver_Log::enabledPush();
	}
}