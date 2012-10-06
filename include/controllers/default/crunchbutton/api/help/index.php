<?php

class Controller_api_help extends Crunchbutton_Controller_Rest {
	public function init() {
		echo json_encode(['data' => c::view()->render('help/index')]);
		exit;
	}
}