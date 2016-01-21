<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init(){

		$driver = Admin::o( 15082 );
		echo '<pre>';var_dump( $driver->statistics( 60 ) );exit();

	}
}
