<?php

class Controller_customers extends Crunchbutton_Controller_Account {
	public function init() {
		c::view()->display('customers/index');
	}
}