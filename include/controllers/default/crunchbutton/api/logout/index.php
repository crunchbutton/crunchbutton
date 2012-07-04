<?php

class Controller_api_logout extends Crunchbutton_Controller_Rest {
	public function init() {
		session_destroy();
	}
}