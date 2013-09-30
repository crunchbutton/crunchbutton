<?php

class Controller_tests_login extends Crunchbutton_Controller_Account {
	public function init() {
		if ( c::admin()->permission()->check( [ 'global','restaurants-all'] ) ) {
			echo 'yes!';
		} else {
			echo  'no!';
		}
		// c::view()->useFilter(false);
		// c::view()->display('tests/login', false);
		exit;
	}
}