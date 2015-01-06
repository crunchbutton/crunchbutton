<?php

class Crunchbutton_Cron_Job_CheckBalanceLimit extends Crunchbutton_Cron_Log {

	public function run(){
		Crunchbutton_Pexcard_Monitor::checkBalanceLimit();

		// it always must call finished method at the end
		$this->finished();
	}
}