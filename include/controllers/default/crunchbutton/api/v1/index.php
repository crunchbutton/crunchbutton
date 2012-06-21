<?php

class Controller_api_v1 extends Crunchbutton_Controller_Rest {
	public function init() {
		echo json_encode(['error' => 'invalid request']);
	}
}