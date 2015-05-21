<?php

class Controller_Api_Test_Sandbox extends Crunchbutton_Controller_Account {
	public function init() {

		// $cron = new Crunchbutton_Cron_Log;
		// $cron->start();
		// cliEnv
		// travis


		$connect = c::config()->db->{ 'crondb' };

		if ($connect->encrypted) {
			$connect->user = c::crypt()->decrypt($connect->user);
			$connect->pass = c::crypt()->decrypt($connect->pass);
		}

		$db = new Cana_Db($connect);

		$users = $db->get( 'SELECT * FROM admin ORDER BY id_admin ASC LIMIT 1 ' );
		echo '<pre>';var_dump( $users );exit();

		Cana::timeout( function() use( $job ) {
			Log::debug([
					'action' => 'login at crondb',
					'type' => 'changed-db'
				]);
		}, 0, true, 'crondb' );
		// $monitor = new Crunchbutton_Cron_Job_LogMonitor;
		// $monitor->run();
	}
}