<?php

class Controller_listorders extends Crunchbutton_Controller_Account {
	public function init() {
		c::view()->layout('layout/basic');
		c::view()->display('listorders/index');
	}
}