<?php

class Controller_test extends Crunchbutton_Controller_Account {
	public function init() {
		c::config()->domain->theme = 'test';
		c::buildView(['layout' =>  c::config()->defaults->layout]);
		c::view()->useFilter(false);

		c::view()->display('test/index');
	}
}