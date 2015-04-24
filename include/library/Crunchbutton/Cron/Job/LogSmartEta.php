<?php

class Crunchbutton_Cron_Job_LogSmartEta extends Crunchbutton_Cron_Log {

	public function run(){

		Crunchbutton_Order_Eta::registerSmartETA();

		// it always must call finished method at the end
		$this->finished();
	}
}