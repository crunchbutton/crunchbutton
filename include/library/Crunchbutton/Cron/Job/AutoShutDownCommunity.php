<?php

class Crunchbutton_Cron_Job_AutoShutDownCommunity extends Crunchbutton_Cron_Log {

	public function run(){

		Crunchbutton_Community::shutDownCommunities();
		// reopen restaurants #7991
		Restaurant::reopenRestaurantsForBusiness();
		// it always must call finished method at the end
		$this->finished();
	}
}
