<?php
class Controller_test_cron extends Crunchbutton_Controller_Account {
	public function init() {
		echo '<pre>';
		echo 'outside';

		$log = new Crunchbutton_Test();

		var_dump(exec('ls'));

		$test = $log;
		
		// Timeout to run it asyncs
			Cana::timeout( function() use( $test ) {
				$test->register( date( 'Y-m-d H:i:s' ) );	
			} );
		// Crunchbutton_Cron_Log::start();
	}
}