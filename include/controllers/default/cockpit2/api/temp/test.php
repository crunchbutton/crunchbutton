<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {
	public function init() {
		echo '<pre>';var_dump( c::env(), c::db() );exit();
	}
}