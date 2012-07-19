<?php

class Controller_api_test extends Crunchbutton_Controller_Rest {
	public function init() {


		c::auth()->session()->generateAndSaveToken();
		echo c::auth()->session()->token;
		exit;
		$o = new Order(34);
		$o->notify();		
	}
}