<?php
class Controller_api_temp_test extends Crunchbutton_Controller_Rest {
	public function init() {
		$job = Crunchbutton_Cron_Job_CommunityNotification::o(119);
		$job->run();
		echo '<pre>';var_dump( 1 );
		$job = Crunchbutton_Cron_Job_RestaurantFixNotify::o(28);
		$job->run();
		echo '<pre>';var_dump( 2 );
	}
}