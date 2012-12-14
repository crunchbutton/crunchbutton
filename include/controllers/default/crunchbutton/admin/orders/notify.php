<?php

class Controller_admin_orders_notify extends Crunchbutton_Controller_Account {
	public function init() {

		$order = new Order(c::getPagePiece(3));
		if (!$order->id_order) {
			die('invalid order');
		}
		$order->notify();
	}
}