<?php

class Crunchbutton_Cron_Job_CommunityNotification extends Crunchbutton_Cron_Log {
	public function run(){
		Community_Notification::job();
		$this->finished();
	}
}
