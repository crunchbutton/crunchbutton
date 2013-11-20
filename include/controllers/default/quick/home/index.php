<?php

class Controller_home extends Crunchbutton_Controller_Account {
	public function init() {
		$order = Order::o(c::db()->escape(c::getPagePiece(0)));
		if (!$order->id_order) {
			exit;
		}
		c::view()->order = $order;
		c::view()->layout('layout/html');
		c::view()->display('order/index');
		exit;
	}
}