<?php

class Controller_api_session extends Crunchbutton_Controller_Rest {
	public function init() {
		echo json_encode([c::auth()->session()->id]);
	}
}