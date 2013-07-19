<?php

class Controller_orders extends Crunchbutton_Controller_Account {
	public function init() {
		c::view()->display('orders/index');
	}
}