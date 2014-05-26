<?php

class Crunchbutton_Cron_Job_Test extends Crunchbutton_Cron_Log {

	public function run(){

		Log::debug( [ 'type' => 'cron-jobs', 'desc' => 'testing the cron log' ] );	
		
		// it always must call finished method at the end
		$this->finished();
	}
}