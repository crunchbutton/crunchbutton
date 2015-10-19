<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {
	public function init() {
		$driver = Admin::o( 5 );
		echo '<pre>';var_dump( $driver->openedCommunity() );exit();
	}
}