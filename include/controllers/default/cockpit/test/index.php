<?php

class Controller_test extends Crunchbutton_Controller_Account {
	public function init() {
	
		$id = 'CC7kIW3yIIOJfFJl5fvDy8hG';
		 c::balanced();

		echo c::balanced()->cards->uri.'/'.$id;

		exit;
	
		$card = Crunchbutton_Balanced_Card::byId($id);
		echo $card->uri;
		exit;
		
		
		
		die(c::balanced()->uri);
	
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