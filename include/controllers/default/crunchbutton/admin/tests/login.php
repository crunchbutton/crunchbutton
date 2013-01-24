<?php

class Controller_admin_tests_login extends Crunchbutton_Controller_Account {
	public function init() {
		c::view()->useFilter(false);
		c::view()->display('admin/tests/login', false);
		exit;
	}
}