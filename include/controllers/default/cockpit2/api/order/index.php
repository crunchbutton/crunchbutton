<?php

class Controller_api_order extends Crunchbutton_Controller_RestAccount {
	
	public function init() {

		$order = Order::uuid(c::getPagePiece(2));
		/* @var $order Crunchbutton_Order */
		if (!$order->id_order) {
			$order = Order::o(c::getPagePiece(2));
		}
		

		$_POST = [
			'name' => 'MR TEST DEVIN',
			'subtotal' => '11.50',
			'tip' => '2.00',
			'phone' => '_PHONE_',
			'address' => '123 main santa monica',
			'card' => [
				'id' => 'CC1yW7tINe5OHE77eplt5hPs',
				'uri' => '/cards/CC1yW7tINe5OHE77eplt5hPs',
				'lastfour' => '4242',
				'card_type' => 'visa',
				'month' => '2',
				'year' => '2016'
			],
			'pay_type' => 'card',
			'delivery_type' => 'delivery',
			'restaurant' => '26',
			'notes' => 'test'
		];


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
						'id_user' => c::auth()->session()->id_user,
						'txn' => $order->txn,
						'final_price' => $order->final_price,
						'uuid' => (new Order($order->id_order))->uuid,
						'token' => c::auth()->session()->token
					]);
				} else {
					echo json_encode(['status' => 'false', 'errors' => $charge]);
				}
				break;
		}
	}
}