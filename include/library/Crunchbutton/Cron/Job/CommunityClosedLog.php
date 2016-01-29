<?php

class Crunchbutton_Cron_Job_CommunityClosedLog extends Crunchbutton_Cron_Log {

	public function run(){

		// Cockpit_Community_Closed_Log::processLog();

		// it always must call finished method at the end
		$this->finished();
	}
}