<?php
error_reporting(0);

class Controller_test_notification extends Crunchbutton_Controller_Account {
	public function init() {
		$order = Order::uuid( c::getPagePiece(2) );
		$order->notify();
		exit;
	}
}