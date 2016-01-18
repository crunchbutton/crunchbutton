<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init(){


		$admin = Admin::o( 1 );
		echo $admin->name . "\n";
		echo "isDriver: " . $admin->isDriver() . "\n";
		echo "isMarketingRep: " . $admin->isMarketingRep() . "\n";
		echo "isCampusManager: " . $admin->isCampusManager() . "\n";
		echo "isSupport: " . $admin->isSupport() . "\n";
		die('hard');


	}
}
