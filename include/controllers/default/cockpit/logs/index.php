<?php

class Controller_logs extends Crunchbutton_Controller_Account {
	public function init() {
		c::view()->display('logs/index');
	}
}