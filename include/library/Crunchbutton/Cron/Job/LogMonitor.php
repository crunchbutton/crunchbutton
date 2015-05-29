<?php

class Crunchbutton_Cron_Job_LogMonitor extends Crunchbutton_Cron_Log {

	public function run(){

		$monitor = new Crunchbutton_Log_Monitor;
		$monitor->run();

		// it always must call finished method at the end
		$this->finished();
	}
}