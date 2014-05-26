<?php

class Crunchbutton_Cron_Job_DriversBeforeTheirShift extends Crunchbutton_Cron_Log {

	public function run(){

		// Crunchbutton_Community_Shift::sendWarningToDrivers();
		
		// it always must call finished method at the end
		$this->finished();
	}
}