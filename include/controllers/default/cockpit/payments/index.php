<?php

class Controller_payments extends Crunchbutton_Controller_Account {
	public function init() {

		if (!c::admin()->permission()->check(['global'])) {
			return ;
		}
		
		c::view()->display('payments/index');
	}
}