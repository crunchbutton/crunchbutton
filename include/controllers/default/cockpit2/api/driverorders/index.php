<?php

class Controller_api_driverorders extends Crunchbutton_Controller_RestAccount {
	public function init() {
		// return a list of orders based on params. show none with no params
		
		$exports = [];

		$orders = Order::deliveryOrders(12); // last 12 hours
		
		foreach ($orders as $order) {
			$exports[] = Model::toModel([
				'id_order' => $order->id_order,
				'lastStatus' => $order->deliveryLastStatus(),
				'name' => $order->name,
				'phone' => $order->phone,
				'date' => $order->date(),
				'restaurant' => $order->restaurant()->name,
			]);
		}

//		if( !$justMineOrders || ( $justMineOrders && $order->lastStatus[ 'id_admin' ] == c::admin()->id_admin ) ){

		usort($exports, function($a, $b) {
			if ($a->lastStatus['status'] == $b->lastStatus['status']) {
				return $a->id_order < $b->id_order;
			}
			return ($a->lastStatus['order'] > $b->lastStatus['order']);
		});
		
		echo json_encode($exports);

	}
}