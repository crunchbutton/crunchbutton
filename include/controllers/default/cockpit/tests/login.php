<?php

class Controller_tests_login extends Crunchbutton_Controller_Account {
	public function init() {
		c::view()->useFilter(false);
		c::view()->display('tests/login', false);
		exit;
	}
}