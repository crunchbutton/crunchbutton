<?php

class Controller_api_config extends Crunchbutton_Controller_Rest {
	public function init() {
		$config = [];
		$config['user'] = c::user()->exports();
		echo json_encode($config);
	}
}