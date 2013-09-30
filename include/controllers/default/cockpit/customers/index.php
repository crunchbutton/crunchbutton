<?php

class Controller_customers extends Crunchbutton_Controller_Account {
	public function init() {
		if (!c::admin()->permission()->check(['global'])) {
			return ;
		}
		c::view()->display('customers/index');
	}
}