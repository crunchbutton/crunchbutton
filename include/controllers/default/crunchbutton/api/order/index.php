<?php

class Controller_api_order extends Crunchbutton_Controller_Rest {
	public function init() {

		$order = Order::uuid(c::getPagePiece(2));
		if (!$order->id_order) {
			$order = Order::o(c::getPagePiece(2));
		}

		switch (c::getPagePiece(3)) {
			case 'refund':
				if (!$order->get(0)->refund()) {
					echo json_encode(['status' => 'false', 'errors' => 'failed to refund']);
					exit;
				}
				break;

			case 'say':
			    header('Content-type: text/xml');
				echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"
					.'<Response><Say voice="female" loop="3">'.htmlspecialchars($order->message('phone')).'</Say></Response>';
					exit;
				break;
				
			case 'doconfirm':
			    header('Content-type: text/xml');
				echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";

				switch ($this->request()['Digits']) {
					case '1':
						echo '<Response><Say voice="female" loop="3">Thank you. This order has been confirmed.</Say></Response>';
						$order->confirmed = 1;
						$order->save();
						break;

					case '2':
						echo '<Response><Say voice="female" loop="3">Thank you. We will resend the order confirmation.</Say></Response>';
						$order->que();
						break;

					default:
						echo '<Response><Gather action="/order/'.$order->id_order.'/doconfirm" numDigits="1" timeout="10" finishOnKey="12" method="get"><Say voice="female" loop="3">Hi. this is Crunchbutton. Please press 1 to confirm that you just received order number '.$order->id_order.'. Or press 2 and we will resend the order.</Say></Gather></Response>';
						break;
				}
				
				exit;
				break;
		}

		switch ($this->method()) {
			case 'get':
				if (get_class($order) != 'Crunchbutton_Order') {
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