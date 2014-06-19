<?php

class Controller_api_order extends Crunchbutton_Controller_RestAccount {
	
	public function init() {

		$order = Order::uuid(c::getPagePiece(2));
		/* @var $order Crunchbutton_Order */
		if (!$order->id_order) {
			$order = Order::o(c::getPagePiece(2));
		}
		

		// @todo check to see if the restaurant has the permissions for that restaurant id
		// $_POST['restaurant']


		switch ($this->method()) {
			case 'get':
				if (get_class($order) != 'Cockpit_Order') {
					$order = $order->get(0);
				}

				if ($order->id_order) {
					echo $order->json();
					break;

				} else {
					echo json_encode(['error' => 'invalid object']);
				}
				break;

			case 'post':
				$order = new Order;
				
				// card, subtotal, tip, name, phone, address
				$charge = $order->process($_POST, 'restaurant');
				if ($charge === true) {
					echo json_encode([
						'id_order' => $order->id_order,
						'id_user' => $order->user()->id_user,
						'final_price' => $order->final_price,
						'uuid' => (new Order($order->id_order))->uuid
					]);
				} else {
					echo json_encode(['status' => 'false', 'errors' => $charge]);
				}
				break;
		}
	}
}