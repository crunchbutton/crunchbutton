<?php

class Crunchbutton_Cron_Job_CommunityClosedLog extends Crunchbutton_Cron_Log {

	public function run(){

		CommunityClosedLog::save_log();

		// it always must call finished method at the end
		$this->finished();
	}
}