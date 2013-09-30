<?php

class Controller_ordrin extends Crunchbutton_Controller_Account {
	public function init() {

		if (!c::admin()->permission()->check(['global'])) {
			return ;
		}

		c::view()->useFilter(false);
		c::view()->layout('layout/blank');
		c::view()->display('ordrin/index');
	}
}