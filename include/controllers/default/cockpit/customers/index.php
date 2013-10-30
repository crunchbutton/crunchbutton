<?php

class Controller_customers extends Crunchbutton_Controller_Account {
	public function init() {
		if (!c::admin()->permission()->check(['global','customers-all'])) {
			return ;
		}
		c::view()->display('customers/index');
	}
}