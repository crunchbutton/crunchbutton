<?php

class Controller_Api_Test_Sandbox extends Crunchbutton_Controller_Account {
	public function init() {

		// $cron = new Crunchbutton_Cron_Log;
		// $cron->start();
		// cliEnv
		// travis



		Cana::timeout( function() use( $job ) {
			Log::debug([
					'action' => 'log at travis',
					'type' => 'changed-db'
				]);
		}, 0, true, 'crondb' );
		// $monitor = new Crunchbutton_Cron_Job_LogMonitor;
		// $monitor->run();
	}
}