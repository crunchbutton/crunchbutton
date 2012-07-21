<?php

class Controller_api_legal extends Crunchbutton_Controller_Rest {
	public function init() {
		echo json_encode(['data' => c::view()->render('legal/index')]);
		exit;
	}
}