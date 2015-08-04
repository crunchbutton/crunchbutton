<?php

class Crunchbutton_Cron_Job_DriversRemindMinutesAboutTheirShift extends Crunchbutton_Cron_Log {

	public function run(){

		Crunchbutton_Community_Shift::warningDriversBeforeTheirShift();
		// Crunchbutton_Admin_Shift_Assign_Confirmation::warningDriversBeforeTheirShift();

		// it always must call finished method at the end
		$this->finished();
	}
}