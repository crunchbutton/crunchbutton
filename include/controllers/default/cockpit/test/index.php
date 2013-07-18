<?php

class Controller_test extends Crunchbutton_Controller_Account {
	public function init() {

		c::config()->site->config('support-phone-afterhours')->set('123');
		c::config()->site->config('xxx')->set('123');
		Crunchbutton_Config::store('xxx','444');

		exit;
		print_r(c::config()->site->config('support-phone-afterhours')->val());

		exit;

		c::config()->domain->theme = 'test';
		c::buildView(['layout' =>  c::config()->defaults->layout]);
		c::view()->useFilter(false);

		c::view()->display('test/index');
	}
}