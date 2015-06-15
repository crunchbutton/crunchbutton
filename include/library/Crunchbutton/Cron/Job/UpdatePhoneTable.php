<?php

class Crunchbutton_Cron_Job_UpdatePhoneTable extends Crunchbutton_Cron_Log {

	public function run(){

		Phone::updatePhoneList();

		// it always must call finished method at the end
		$this->finished();
	}
}