<?php

class Controller_logs extends Crunchbutton_Controller_Account {
	public function init() {
		// @permission
		if (!c::admin()->permission()->check(['global'])) {
			return ;
		}
		c::view()->display('logs/index');
	}
}