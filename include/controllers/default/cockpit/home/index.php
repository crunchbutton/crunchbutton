<?php

class Controller_home extends Crunchbutton_Controller_Account {
	public function init() {
		c::view()->display('home/index');
	}
}