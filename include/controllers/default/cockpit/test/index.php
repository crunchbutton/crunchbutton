<?php

class Controller_test extends Crunchbutton_Controller_Account {
	public function init() {

		$n = 1;
		echo $n - 100  * floor($n / 100) . rand(1,1000) . floor($n/100);
		exit;
		echo Base36::encode('12345');
		exit;
		c::admin()->restaurants();
		exit;
		$r = new Restaurant(21);


		$time = '2013-10-13 00:12:00';
//$time = new DateTime($time, new DateTimeZone($r->timezone));
//echo $time->format('Y-m-d H:i:s');


		if ($r->open($time)) {
			echo 'open';
		} else {
			echo 'closed';
		}
		
		exit;
	
		if (c::admin()->permission()->check(['test','testsss'])) {
			echo 'true';
		} else {
			echo 'false';
		}
		
		exit;
		

		echo c::balanced()->uri;
		exit;

		$r = new Restaurant;
		$r->name = 'asd';
		$r->save();
		exit;
		c::config()->site->config('support-phone-afterhours')->set('123');
		c::config()->site->config('xxx')->set('123');
		Crunchbutton_Config::store('xxx','444');


		print_r(c::config()->site->config('support-phone-afterhours')->val());

		exit;

		c::config()->domain->theme = 'test';
		c::buildView(['layout' =>  c::config()->defaults->layout]);
		c::view()->useFilter(false);

		c::view()->display('test/index');
	}
}