<?php

class Controller_orders_notify extends Crunchbutton_Controller_Account {
	public function init() {
		if (!c::admin()->permission()->check(['global','orders-all','orders-notification'])) {
			return ;
		}
		$order = new Order(c::getPagePiece(3));
		if (!$order->id_order) {
			die('invalid order');
		}
		$order->notify();
	}
}