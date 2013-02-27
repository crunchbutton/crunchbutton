<?php

class Controller_admin_test extends Crunchbutton_Controller_Account {
	public function init() {
//		Cana::view()->layout('layout/ajax');

		c::config()->domain->theme = 'test';
		c::buildView(['layout' =>  c::config()->defaults->layout]);
		c::view()->useFilter(false);

		c::view()->display('admin/test/index');
	}
}