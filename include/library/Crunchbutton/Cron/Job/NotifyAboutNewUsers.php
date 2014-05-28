<?php

class Crunchbutton_Cron_Job_NotifyAboutNewUsers extends Crunchbutton_Cron_Log {

	public function run(){

		Crunchbutton_Newusers::sendEmailCLI();
		
		// it always must call finished method at the end
		$this->finished();
	}
}