<?php

class Controller_tests_svb extends Crunchbutton_Controller_Account {
	public function init() {
		c::view()->useFilter(false);
		c::view()->display('tests/svb', false);
		exit;
	}
}