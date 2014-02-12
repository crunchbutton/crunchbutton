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

				if ( !c::admin()->permission()->check(['global','orders-all','orders-refund'])) {
					return ;
				}

				if (!$order->get(0)->refund()) {
					echo json_encode(['status' => 'false', 'errors' => 'failed to refund']);
					exit;
				}
				break;

			case 'pay_if_refunded':

				if ( !c::admin()->permission()->check(['global','orders-all','orders-refund'])) {
					return ;
				}

				$order->pay_if_refunded = c::getPagePiece(4);
				$order->save();
				echo json_encode(['status' => 'success']);
				exit;
				break;

			case 'resend_notification':

				if ( !c::admin()->permission()->check(['global','orders-all','orders-notification'])) {
					return ;
				}

				if ( $order->resend_notify() ) {
					echo json_encode(['status' => 'success']);
					exit;
				} else {
					echo json_encode(['status' => 'error']);
				}
				break;

			case 'say':
				header('Content-type: text/xml');
				$message = '<Say voice="'.c::config()->twilio->voice.'">Press 1 to hear the order. Otherwise we will call back in 2 minutes.</Say>'
						.'<Pause length="5" />';

				echo '<?xml version="1.0" encoding="UTF-8"?><Response>'."\n"
					.'<Gather action="/api/order/'.$order->id_order.'/sayorder?id_notification='.$_REQUEST['id_notification'].'" numDigits="1" timeout="10" finishOnKey="#" method="get">'
					.'<Say voice="'.c::config()->twilio->voice.'">'.c::config()->twilio->greeting.' with an order for '.($order->delivery_type == 'delivery' ? 'delivery' : 'pickup').'.</Say>'
					.'<Pause length="1" />';

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
							'host' => c::config()->host_callback,
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

			case 'sayorderadmin':
				header('Content-type: text/xml');
				echo '<?xml version="1.0" encoding="UTF-8"?><Response>'."\n";

				$cockipt_url = Crunchbutton_Admin_Notification::REPS_COCKPIT . $order->id_order;

				$pauseRepeat =
						'<Pause length="1" />'
						.'<Say voice="'.c::config()->twilio->voice.'">Press 1 to repeat the order. '.($order->delivery_type == 'delivery' ? 'Press 2 to spell out the street name.' : '').'
						 Press 3 to spell out the cockpit url.
						</Say>';

				switch ($this->request()['Digits']) {
					case '3':
						echo '<Gather action="/api/order/'.$order->id_order.'/sayorderadmin" numDigits="1" timeout="10" finishOnKey="#" method="get">';
						echo Crunchbutton_Admin_Notification::spellOutURL( $order->id_order );

						for ($x = 0; $x <= $repeat; $x++) {
							echo $pauseRepeat;
						}

						echo '</Gather>';
						break;
					case '2':
						echo '<Gather action="/api/order/'.$order->id_order.'/sayorderadmin" numDigits="1" timeout="10" finishOnKey="#" method="get">'
							.$order->streetName();

						for ($x = 0; $x <= $repeat; $x++) {
							echo $pauseRepeat;
						}

						echo '</Gather>';
						break;
					case '1':
					default:
						echo '<Gather action="/api/order/'.$order->id_order.'/sayorderadmin" numDigits="1" timeout="10" finishOnKey="#" method="get">'
							.'<Say voice="'.c::config()->twilio->voice.'">'.c::config()->twilio->greeting.' with an order for '.($order->delivery_type == 'delivery' ? 'delivery' : 'pickup').'.</Say>'
							.'<Say voice="'.c::config()->twilio->voice.'">'.$order->message('phone').'</Say>';
						
						echo '<Say voice="male"> <![CDATA[ Access ' . $cockipt_url . '  ]]> </Say>';

						for ($x = 0; $x <= $repeat; $x++) {
							echo $pauseRepeat;
						}

						echo '</Gather>';
						break;
				}
				echo '</Response>';
				exit;
				break;

			case 'driver-first-call-warning':
			case 'driver-second-call-warning':
				if( c::getPagePiece(3) == 'driver-second-call-warning' ){
					$message = 'Confirm the order . otherwise . a Crunchbutton customer service representative will call you shortly. . .';
				} else {
					$message = 'Confirm the order . otherwise . we will call back . . ';
				}
				header('Content-type: text/xml');
				echo '<?xml version="1.0" encoding="UTF-8"?><Response>'."\n";
						echo '<Gather action="/api/order/'.$order->id_order.'/sayorderadmin" numDigits="1" timeout="10" finishOnKey="#" method="get">'
							. '<Pause length="1" />'
							. '<Say voice="'.c::config()->twilio->voice.'">'.c::config()->twilio->greeting.' . . </Say>'
							.'<Say voice="'.c::config()->twilio->voice.'">You have not confirmed order number ' . $order->id_order . ' . . </Say>' 
							.'<Say voice="'.c::config()->twilio->voice.'">' . $message . '</Say>';
							$pauseRepeat = '<Pause length="1" /><Say voice="'.c::config()->twilio->voice.'">Press 1 to hear the order. </Say>';
							for ($x = 0; $x <= $repeat; $x++) {
								echo $pauseRepeat;
							}
							echo '</Gather>';
				echo '</Response>';
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
							'host' => c::config()->host_callback,
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
							// The confirmation was already sent #1049
							// $order->receipt();
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

			// Issue #1250 - make Max CB a phone call in addition to a text
			case 'maxconfirmation' : 
				header('Content-type: text/xml');
				echo '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<Response>';
					echo '<Say voice="'.c::config()->twilio->voice.'">';
					echo 'Max confirmation call for order number ' . $order->id_order . ' has timed out to ' . htmlentities( $order->restaurant()->name ) . ' from ' . $order->name;
					echo '</Say>';
					echo '</Response>';
				exit;
				break;

			case 'maxcalling' : 
				header('Content-type: text/xml');
					echo '<?xml version="1.0" encoding="UTF-8"?>';
					echo '<Response>';
					switch ($this->request()['Digits']) {
						case 1:
							if( $_REQUEST['id_notification'] ){
								$notification = Notification_Log::o( $_REQUEST['id_notification'] );	
							} else {
								$notification = Notification_Log::getMaxCallNotification( $order->id_order );	
							}
							if( $notification->id_notification_log ){
									$notification->status = 'success';
									$notification->data = json_encode($_REQUEST);
									$notification->save();
								Log::debug( [ 'order' => $notification->id_order, 'action' => 'MAX CB - confirmed', 'data' => json_encode($_REQUEST), 'id_notification_log'=> $notification->id_notification_log, 'type' => 'notification' ]);
							} else {
								Log::debug( [ 'order' => $notification->id_order, 'action' => 'MAX CB - confirmation error', 'data' => json_encode($_REQUEST), 'type' => 'notification' ]);
							}
							echo '<Say voice="'.c::config()->twilio->voice.'">';
								echo 'Thank you . ';
							echo '</Say>';
							break;
						default:

							$notification = Notification_Log::getMaxCallNotification( $order->id_order );	
							$types = $order->restaurant()->notification_types();
							if( count( $types ) > 0 ){
								$notifications = 'Restaurant notifications . ' . join( ' . ', $types );
							} else {
								$notifications = '';
							}

							Log::debug( [ 'order' => $order->id_order, 'id_notification' => $notification->id_notification_log ,'action' => 'MAX CB', 'data' => json_encode($_REQUEST), 'type' => 'notification' ]);
							
							echo '<Gather action="/api/order/'.$order->id_order.'/maxcalling?id_notification='.$_REQUEST['id_notification'].'" numDigits="1" timeout="10" finishOnKey="#" method="get">';
								echo '<Say voice="'.c::config()->twilio->voice.'">';
								echo 'Max call back for order number ' . $order->id_order . ' has timed out to ' . htmlentities( $order->restaurant()->name ) . ' from ' . $order->name;
									echo '</Say>';
								echo '<Pause length="1" />';
								echo '<Say voice="'.c::config()->twilio->voice.'">';
								echo $notifications;
									echo '</Say>';
									echo '<Pause length="1" />';
								echo '<Say voice="'.c::config()->twilio->voice.'">';
									echo Notification_Log::maxCallMSayAtTheEndOfMessage();
								echo '</Say>';
							echo '</Gather>';
							break;
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
							'host' => c::config()->host_callback,
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
							// The confirmation was already sent #1049
							// $order->receipt();
						}
						break;

					case '2':
						Log::debug([
							'order' => $order->id_order,
							'action' => 'RESEND',
							'host' => c::config()->host_callback,
							'type' => 'notification'
						]);

						echo '<Say voice="'.c::config()->twilio->voice.'">Thank you. We will resend the order confirmation.</Say>';
						$order->que( false );
						break;
					case '0':
						echo '<Dial timeout="10" record="true">'.c::config()->phone->restaurant.'</Dial>';

					default:
					case '3':
					case '4':
					case '5':
					case '6':
					case '7':
					case '8':
					case '9':
					case '#':
					case '*':
						echo '<Gather action="/api/order/'.$order->id_order.'/doconfirm" numDigits="1" timeout="10" finishOnKey="#" method="get">'
							. '<Say voice="'.c::config()->twilio->voice.'">'.c::config()->twilio->greeting.'.</Say>' 
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