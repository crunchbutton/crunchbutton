<?php

class Controller_admin_test extends Crunchbutton_Controller_Account {
	public function init() {
	
		$geo = new Crunchbutton_Geo([
			'adapter' => 'Geoip_Binary',
			'file' => c::config()->dirs->root.'db/GeoLiteCity.dat'
		]);
		$geo->setIp('76.90.138.20')->populateByIp();

		print_r($geo);

		exit;
//		Cana::view()->layout('layout/ajax');

		c::config()->domain->theme = 'test';
		c::buildView(['layout' =>  c::config()->defaults->layout]);
		c::view()->useFilter(false);

		c::view()->display('admin/test/index');
	}
}