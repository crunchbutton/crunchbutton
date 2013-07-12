<?php

class Controller_test extends Crunchbutton_Controller_Account {
	public function init() {
		echo ini_get('upload_max_filesize');
		echo ini_get('post_max_size');
		exit;

		c::config()->domain->theme = 'test';
		c::buildView(['layout' =>  c::config()->defaults->layout]);
		c::view()->useFilter(false);

		c::view()->display('test/index');
	}
}