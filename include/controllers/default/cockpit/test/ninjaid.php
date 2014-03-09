<?php

class Controller_test_ninjaid extends Crunchbutton_Controller_Account {
	public function init() {
		$tests = [1234, 15621, 125464, 6234627, 12223334, 122233344];
		
		foreach ($tests as $test) {
			$o = Order::o($test);
			$n = $o->ninjaId();
			$d = Order::getByNinjaId($n);
			echo $o->id.'<br>'.$n.'<br>'.$d.'<br><br>';
		}

		exit;
	
	}
}