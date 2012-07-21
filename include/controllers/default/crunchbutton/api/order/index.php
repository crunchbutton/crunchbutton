<?php

class Controller_api_order extends Crunchbutton_Controller_Rest {
	public function init() {

		$order = Order::o(c::getPagePiece(2));
		if (!$order->id_order) {
			$order = Order::uuid(c::getPagePiece(2));
		}

		switch (c::getPagePiece(3)) {
			case 'say':
				$say = 'tester';
			    header('Content-type: text/xml');
				echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"
					.'<Response><Say voice="female" loop="3">'.htmlspecialchars($order->message('phone')).'</Say></Response>';
					exit;
				break;
		}
						
		switch ($this->method()) {
			case 'get':
//				$order = $order->get(0);
				if ($order->id_order) {
					$say = 'this is a test';
					echo $order->json();
					break;

				} else {
					echo json_encode(['error' => 'invalid object']);
				}
				break;

			case 'post':
				$order = new Order;
				$charge = $order->process($this->request());
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