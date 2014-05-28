<?php

class Crunchbutton_Cron_Job_Test extends Crunchbutton_Cron_Log {

	public function run(){

		Log::debug( [ 'desc' => 'testing the cron log', 'type' => 'cron-jobs' ] );	

		// it always must call finished method at the end
		$this->finished();
	}
}