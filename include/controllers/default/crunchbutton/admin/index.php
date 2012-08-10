<?php

class Controller_admin extends Crunchbutton_Controller_Account {
	public function init() {
		c::view()->layout('layout/admin');
		c::view()->display('admin/index');
	}
}