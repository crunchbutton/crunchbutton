<?php
class Controller_test_cron extends Crunchbutton_Controller_Account {
	public function init() {
		echo '<pre>';
		Crunchbutton_Cron_Log::start();
	}
}