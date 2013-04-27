<?php

class Controller_pay extends Crunchbutton_Controller_Account {
	public function init() {
		c::view()->display('pay/index');
	}
}