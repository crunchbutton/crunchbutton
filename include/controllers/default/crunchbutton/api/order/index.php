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
				echo '<?xml version="1.0" encoding="UTF-8"?><Response>'."\n"
					.'<Say voice="'.c::config()->twilio->voice.'">'.c::config()->twilio->greeting.' with an order for you.</Say>'
					.'<Pause length="1" />'
					.'<Gather action="/api/order/'.$order->id_order.'/sayorder" numDigits="1" timeout="20" finishOnKey="#" method="get">'
						.'<Say voice="'.c::config()->twilio->voice.'">'.$order->message('phone').'</Say>'
						.'<Pause length="2" />'
						.'<Say voice="'.c::config()->twilio->voice.'">Press 1 to repeat this message.</Say>'
					.'</Gather></Response>';
					exit;
				break;
				
			case 'sayorder':
			    header('Content-type: text/xml');
				echo '<?xml version="1.0" encoding="UTF-8"?><Response>'."\n"
					.'<Gather action="/api/order/'.$order->id_order.'/sayorder" numDigits="1" timeout="10" finishOnKey="#" method="get">'
						.'<Say voice="'.c::config()->twilio->voice.'">'.$order->message('phone').'</Say>'
						.'<Pause length="2" />'
						.'<Say voice="'.c::config()->twilio->voice.'">Press 1 to repeat this message.</Say>'
					.'</Gather></Response>';
					exit;
				break;
				
			case 'doconfirm':
			    header('Content-type: text/xml');
				echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"
					.'<Response>';

				switch ($this->request()['Digits']) {
					case '1':
						echo '<Say voice="'.c::config()->twilio->voice.'">Thank you. This order has been confirmed.</Say>';
						$order->confirmed = 1;
						$order->save();
						break;

					case '2':
						echo '<Say voice="'.c::config()->twilio->voice.'">Thank you. We will resend the order confirmation.</Say>';
						$order->que();
						break;
					case '0':
						echo '<Dial timeout="10" record="true">213-293-6935</Dial>';

					default:
						echo '<Say voice="'.c::config()->twilio->voice.'">'.c::config()->twilio->greeting.'.</Say>';
					case '3':
					case '4':
					case '5':
					case '6':
					case '7':
					case '8':
					case '9':
					case '#':
					case '*':					
						echo '<Gather action="/api/order/'.$order->id_order.'/doconfirm" numDigits="1" timeout="10" finishOnKey="12" method="get">'
							.'<Say voice="'.c::config()->twilio->voice.'" loop="3">Please press 1 to confirm that you just received order number '.$order->id_order.'. Or press 2 and we will resend the order. . . .</Say>'
							.'</Gather>';
						break;
				}
				
				echo '</Response>';
				
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