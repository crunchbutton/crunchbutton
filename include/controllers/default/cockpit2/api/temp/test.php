<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

public function init() {

		$admin = Admin::o( 2297 );
		echo '<pre>';var_dump( $admin->statistics( 60 ) );exit();

	}
}
