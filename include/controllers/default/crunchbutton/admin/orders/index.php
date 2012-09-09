<?php

class Controller_admin_orders extends Crunchbutton_Controller_Account {
	public function init() {
		c::view()->layout('layout/admin');
		c::view()->display('admin/orders/index');
	}
}