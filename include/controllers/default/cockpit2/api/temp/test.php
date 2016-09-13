<?php
class Controller_api_temp_test extends Crunchbutton_Controller_Rest {
	public function init() {
		$drivers = Admin::q('SELECT * FROM admin WHERE active = 1');
		foreach($drivers as $driver){
			if($driver->isDriver()){
				$driver->addPermissions(['community-cs' => true]);
			}
		}
	}
}