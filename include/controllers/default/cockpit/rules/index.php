<?php

class Controller_Rules extends Crunchbutton_Controller_Account {
	public function init() {
		if (!c::admin()->permission()->check(['global','rules'])) {
			return ;
		}
		c::view()->rules = new Crunchbutton_Order_Rules();
		c::view()->display('rules/index');
	}
}