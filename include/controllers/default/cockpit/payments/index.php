<?php

class Controller_payments extends Crunchbutton_Controller_Account {
	public function init() {
		c::view()->display('payments/index');
	}
}