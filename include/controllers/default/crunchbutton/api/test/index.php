<?php

class Controller_api_test extends Crunchbutton_Controller_Rest {
	public function init() {
		echo c::getEnv()."\n";
		echo c::env()."\n";
		echo $_SERVER['SERVER_NAME'];
	}
}