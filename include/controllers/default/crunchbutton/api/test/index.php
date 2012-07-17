<?php

class Controller_api_test extends Crunchbutton_Controller_Rest {
	public function init() {
		$n = new Notification(15);
		$n->send();
		
	}
}