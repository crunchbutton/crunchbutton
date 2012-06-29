<?php

class Controller_api_config extends Crunchbutton_Controller_Rest {
	public function init() {
		$config = [];
		echo json_encode($config);
	}
}