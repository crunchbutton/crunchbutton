<?php

class Controller_api_notification extends Crunchbutton_Controller_Rest {
	public function init() {

		$notification = Notification_Log::o(c::getPagePiece(2));

						
		switch ($this->method()) {
			case 'get':
				$order = $order->get(0);
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