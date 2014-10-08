<?php

class Crunchbutton_Cron_Job_DriversShiftWarningWeekly extends Crunchbutton_Cron_Log {

	public function run(){

		Crunchbutton_Community_Shift::shiftWarningWeekly();

		// it always must call finished method at the end
		$this->finished();
	}
}