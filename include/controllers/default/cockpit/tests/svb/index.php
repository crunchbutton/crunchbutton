<?php

class Controller_tests_svb extends Crunchbutton_Controller_Account {
	public function init() {
		c::view()->display('tests/svb/index', false);
		exit;
	}
}