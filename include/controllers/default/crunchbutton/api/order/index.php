<?php

class Controller_api_order extends Crunchbutton_Controller_Rest {
	public function init() {

		$order = Order::uuid(c::getPagePiece(2));
		/* @var $order Crunchbutton_Order */
		if (!$order->id_order) {
			$order = Order::o(c::getPagePiece(2));
		}

		$pauseRepeat =
			'<Pause length="1" />'
			.'<Say voice="'.c::config()->twilio->voice.'">Press 1 to repeat the order. Press 2 to confirm the order. '.($order->delivery_type == 'delivery' ? 'Press 3 to spell out the street name.' : '').'</Say>';
		$repeat = 3;

		switch (c::getPagePiece(3)) {
			case 'refund':
				if (!$order->get(0)->refund()) {
					echo json_encode(['status' => 'false', 'errors' => 'failed to refund']);
					exit;
				}
				break;

			case 'say':
				header('Content-type: text/xml');
				$message = '<Say voice="'.c::config()->twilio->voice.'">Press 1 to hear the order. Otherwise we will call back in 2 minutes.</Say>'
						.'<Pause length="5" />';

				echo '<?xml version="1.0" encoding="UTF-8"?><Response>'."\n"
					.'<Say voice="'.c::config()->twilio->voice.'">'.c::config()->twilio->greeting.' with an order for '.($order->delivery_type == 'delivery' ? 'delivery' : 'pickup').'.</Say>'
					.'<Gather action="/api/order/'.$order->id_order.'/sayorder?id_notification='.$_REQUEST['id_notification'].'" numDigits="1" timeout="10" finishOnKey="#" method="get"><Pause length="1" />';

				for ($x = 0; $x <= $repeat; $x++) {
					echo $message;
				}
				echo '</Gather></Response>';
				exit;

				break;

			case 'sayorder':

					Log::debug([
							'order' => $order->id_order,
							'action' => '/sayorder (accepted)',
							'host' => $_SERVER['HTTP_HOST_CALLBACK'],
							'type' => 'notification'
						]);

				$log = new Notification_Log;
				$log->id_notification = $_REQUEST['id_notification'];
				$log->status = 'accepted';
				$log->remote = $_REQUEST['CallSid'];
				$log->type = 'twilio';
				$log->id_order = $order->id_order;
				$log->data = json_encode($_REQUEST);
				$log->date = date('Y-m-d H:i:s');
				$log->save();

				header('Content-type: text/xml');
				echo '<?xml version="1.0" encoding="UTF-8"?><Response>'."\n"
					.'<Gather action="/api/order/'.$order->id_order.'/sayorderonly?id_notification='.$_REQUEST['id_notification'].'" numDigits="1" timeout="10" finishOnKey="#" method="get">'
					.'<Say voice="'.c::config()->twilio->voice.'">Thank you. At the end of the message, you must confirm the order.</Say>'
					.'<Pause length="2" />'
					.'<Say voice="'.c::config()->twilio->voice.'">'.$order->message('phone').'</Say>';

				for ($x = 0; $x <= $repeat; $x++) {
					echo $pauseRepeat;
				}

				echo '</Gather>'
					.'</Response>';
				exit;
				break;

			case 'sayorderonly':
				header('Content-type: text/xml');
				echo '<?xml version="1.0" encoding="UTF-8"?><Response>'."\n";

				switch ($this->request()['Digits']) {
					case '1':
					default:
						echo '<Gather action="/api/order/'.$order->id_order.'/sayorderonly?id_notification='.$_REQUEST['id_notification'].'" numDigits="1" timeout="10" finishOnKey="#" method="get">'
							.'<Say voice="'.c::config()->twilio->voice.'">'.$order->message('phone').'</Say>';

						for ($x = 0; $x <= $repeat; $x++) {
							echo $pauseRepeat;
						}

						echo '</Gather>';
						break;

					case '2':
						Log::debug([
							'order' => $order->id_order,
							'action' => '/sayorderonly: 2: CONFIRMED',
							'host' => $_SERVER['HTTP_HOST_CALLBACK'],
							'type' => 'notification'
						]);
						echo '<Gather action="/api/order/'.$order->id_order.'/sayorderonly?id_notification='.$_REQUEST['id_notification'].'" numDigits="1" timeout="10" finishOnKey="#" method="get">';
						echo '<Say voice="'.c::config()->twilio->voice.'">Order confirmed. Thank you</Say>';
						echo '<Pause length="1" />';
						echo '<Say voice="'.c::config()->twilio->voice.'">If you have any questions, please press 0 or call us at 2. 1. 3. 2. 9. 3. 6. 9. 3. 5.</Say>';
						echo '</Gather>';
						$order->confirmed = 1;
						$order->save();
						if ($order->restaurant()->confirmation) {
							$order->receipt();
						}
						break;

					case '3':
						echo '<Gather action="/api/order/'.$order->id_order.'/sayorderonly?id_notification='.$_REQUEST['id_notification'].'" numDigits="1" timeout="10" finishOnKey="#" method="get">'
							.$order->streetName();

						for ($x = 0; $x <= $repeat; $x++) {
							echo $pauseRepeat;
						}

						echo '</Gather>';
						break;

					case '0':
						echo '<Dial timeout="10" record="true">_PHONE_</Dial>';

				}
				echo '</Response>';
				exit;
				break;

			case 'doconfirm':
				header('Content-type: text/xml');
				echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"
					.'<Response>';

				switch ($this->request()['Digits']) {
					case '1':
						Log::debug([
							'order' => $order->id_order,
							'action' => '/doconfirm: 1: CONFIRMED',
							'host' => $_SERVER['HTTP_HOST_CALLBACK'],
							'type' => 'notification'
						]);
						echo '<Gather action="/api/order/'.$order->id_order.'/sayorderonly?id_notification='.$_REQUEST['id_notification'].'" numDigits="1" timeout="10" finishOnKey="#" method="get">';
						echo '<Say voice="'.c::config()->twilio->voice.'">Order confirmed. Thank you.</Say>';
						echo '<Pause length="1" />';
						echo '<Say voice="'.c::config()->twilio->voice.'">If you have any questions, please press 0 or call us at 2. 1. 3. 2. 9. 3. 6. 9. 3. 5.</Say>';
						echo '</Gather>';
						$order->confirmed = 1;
						$order->save();
						if ($order->restaurant()->confirmation) {
							$order->receipt();
						}
						break;

					case '2':
						Log::debug([
							'order' => $order->id_order,
							'action' => 'RESEND',
							'host' => $_SERVER['HTTP_HOST_CALLBACK'],
							'type' => 'notification'
						]);

						echo '<Say voice="'.c::config()->twilio->voice.'">Thank you. We will resend the order confirmation.</Say>';
						$order->que();
						break;
					case '0':
						echo '<Dial timeout="10" record="true">'.c::config()->phone->restaurant.'</Dial>';

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
				$charge = $order->process($_POST);
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