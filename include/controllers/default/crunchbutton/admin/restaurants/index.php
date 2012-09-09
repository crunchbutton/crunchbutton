<?php

class Controller_admin_restaurants extends Crunchbutton_Controller_Account {
	public function init() {
		c::view()->layout('layout/admin');
		c::view()->display('admin/restaurants/index');
	}
}