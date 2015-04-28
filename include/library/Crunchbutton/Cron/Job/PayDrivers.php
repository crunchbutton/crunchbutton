<?php

class Crunchbutton_Cron_Job_PayDrivers extends Crunchbutton_Cron_Log {

	public function run(){

		$settlement = new Crunchbutton_Settlement;

		$settlement->doDriverPayments();

		// it always must call finished method at the end
		$this->finished();
	}
}