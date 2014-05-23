<?php
class Controller_test_cron extends Crunchbutton_Controller_Account {
	public function init() {
		Crunchbutton_Cron_Log::start();
	}
}