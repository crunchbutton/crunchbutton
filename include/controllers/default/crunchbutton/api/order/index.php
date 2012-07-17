<?php

class Controller_api_order extends Crunchbutton_Controller_Rest {
	public function init() {
		switch ($this->method()) {
			case 'get':
				$order = Order::o(c::getPagePiece(2));

				if ($order->id_order) {
					$say = 'this is a fucking test';
					switch (c::getPagePiece(3)) {
						case 'say':
							echo '<?xml version="1.0" encoding="UTF-8"?><Response><Say voice="woman" loop="3">'.htmlspecialchars($say).'</Say></Response>';
							break;
						default:
							echo $order->json();
							break;
					}

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
						'final_price' => $order->final_price
					]);
				} else {
					echo json_encode(['status' => 'false', 'errors' => $charge]);
				}
				break;
		}
	}
}