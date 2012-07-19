<?php

class Controller_api_test extends Crunchbutton_Controller_Rest {
	public function init() {
		$o = new Order(64);
		$o->notify();		
	}
}