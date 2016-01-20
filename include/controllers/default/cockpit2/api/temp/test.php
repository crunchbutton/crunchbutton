<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init(){

		$q = Crunchbutton_Queue_Pexcard_Action::o( 33 );
		$q->run();

die('hard');
		$admin = Admin::o( 1 );
		echo $admin->name . "\n";
		echo "isDriver: " . $admin->isDriver() . "\n";
		echo "isMarketingRep: " . $admin->isMarketingRep() . "\n";
		echo "isCampusManager: " . $admin->isCampusManager() . "\n";
		echo "isSupport: " . $admin->isSupport() . "\n";
		die('hard');


	}
}
