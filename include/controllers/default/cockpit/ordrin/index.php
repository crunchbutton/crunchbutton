<?php

class Controller_ordrin extends Crunchbutton_Controller_Account {
	public function init() {
		c::view()->useFilter(false);
		c::view()->layout('layout/blank');
		c::view()->display('ordrin/index');
	}
}