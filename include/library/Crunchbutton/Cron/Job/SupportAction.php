<?php

class Crunchbutton_Cron_Job_SupportAction extends Crunchbutton_Cron_Log {
	public function run(){
		Support_Action::checkStatus();
		$this->finished();
	}
}
