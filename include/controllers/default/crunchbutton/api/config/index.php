<?php

class Controller_api_config extends Crunchbutton_Controller_Rest {
	public function init() {
		echo json_encode(c::appConfig());
	}
}