<?php

class Controller_orders extends Crunchbutton_Controller_Account {
	public function init() {
		if (!c::admin()->permission()->check(['global','orders-all','orders-list-page'])) {
			return ;
		}
		c::view()->display('orders/index');
	}
}