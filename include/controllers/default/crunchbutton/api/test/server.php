<?php

class Controller_api_test_server extends Crunchbutton_Controller_Rest {
	public function init() {
		echo '<pre>';var_dump( $_REQUEST[ 'cockpit' ], $_SERVER['HTTP_HOST'] );exit();
	}
}