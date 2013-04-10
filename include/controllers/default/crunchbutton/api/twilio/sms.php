<?php
class Controller_api_twilio_sms extends Crunchbutton_Controller_Rest {
	public function init() {
		$phone = str_replace('+1','',$_REQUEST['From']);
		$body = trim($_REQUEST['Body']);
		$env = c::env() == 'live' ? 'live' : 'dev';
		$twilio = new Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
		$tsess = Session_Twilio::get();
		$tsess->data = json_encode($_REQUEST);
		$tsess->save();

		header('Content-type: text/xml');
		echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"
			.'<Response>';
			
		foreach (c::config()->text as $supportName => $supportPhone) {
			if ($supportPhone == $phone) {
				$type = 'rep';
				$rep = $supportName;
			}
		}
		
		switch ($type) {
			case 'rep':
				foreach (c::config()->text as $supportName => $supportPhone) {
					if ($supportName == $rep) continue;
					$nums[] = $supportPhone;
				}

				if ($body{0} == '@') {
					$id = str_replace('@','',$body);

					$rsess = new Session_Twilio($id);
					if (!$rsess->id_session_twilio) {
						$msg = 'Invalid ID. Enter a session id to reply to. ex: "@123"';
						$nums = [];
					} else {
						$msg = "$rep is now replying to @".$rsess->id_session_twilio.'. Type a message to respond.';
						$_SESSION['support-respond-sess'] = $rsess->id_session_twilio;
					}

				} elseif ($_SESSION['support-respond-sess']) {
					$rsess = new Session_Twilio($_SESSION['support-respond-sess']);
					$message = $rep.': '.$body;
					
					$nums[] = $rsess->phone;
					
					$b = $message;
					$id = $rsess->id_session_twilio;
					c::timeout(function() use ($nums, $b, $twilio, $env, $id) {

						$opMessage = str_split('@'.$id.'  '.$b,160);
						$message = str_split($b,160);

						foreach ($nums as $i => $num) {
							foreach (($i == 0 ? $message : $opMessage ) as $msg) {
								try {
									$twilio->account->sms_messages->create(
										c::config()->twilio->{$env}->outgoingTextCustomer,
										'+1'.$num,
										$msg
									);
								} catch (Exception $e) {

								}
							}
						}
					});

				} else {
					$msg = 'Invalid ID. Enter a session id to reply to. ex: "@123"';
				}

				echo '<Sms>'.$msg.'</Sms>';
				break;

			default:
				$_SESSION['sms-action'] = 'support-ask';
				if (!$_SESSION['support-order-num']) {
					$order = Order::q('select * from `order` where phone="'.$phone.'" order by date desc limit 1');
				} elseif ($_SESSION['support-order-num'] != 'none') {
					$order = Order::o($_SESSION['support-order-num']);
				} else {
					$order = null;
				}
				if($order) {
					$restaurant = new Restaurant($order->id_restaurant);
					// hard-coding eastern daylight time because that's where
					// all our support is right now. we should think of a solution
					// to this and change it eventually. also right now our db times
					// are all pst. we should change that too.
					$edt_datetime = strtotime($order->date);
					date_default_timezone_set('America/New_York');
					$edt_datetime = date('D, M d, g:i a', $edt_datetime) . ' EDT';
					$last_cb = "Last CB: #$order->id_order, from $restaurant->name, on $edt_datetime.";
				} else {
					$last_cb = 'Last CB: None.';
				}
			
				switch ($_SESSION['sms-action']) {
					case 'support-ask':
						$tsess->id_order = $order ? $order->id_order : null;
						$tsess->phone = $phone;
						$tsess->save();

						$message = '@'.$tsess->id_session_twilio.' ';
						if ($order) {
							$message .= ' #'.$order->id_order.' '.$order->name.': ';
						} else {
							$message .= ': ';
						}
						$message .= htmlspecialchars($body);
						$message = str_split($message,160);

						if(!$_SESSION['last_cb']) {
							$_SESSION['last_cb'] = $last_cb;
							$message[] = $last_cb;
						}

						$b = $message;
						c::timeout(function() use ($b, $env, $twilio) {
							foreach (c::config()->text as $supportName => $supportPhone) {
								foreach ($b as $msg) {
									try {
										$twilio->account->sms_messages->create(
											c::config()->twilio->{$env}->outgoingTextCustomer,
											'+1'.$supportPhone,
											$msg
										);
									} catch (Exception $e) {
									
									}
								}
							}
						});
						break;
		
					default:

							$outgoingTextCustomer = c::config()->twilio->{$env}->outgoingTextCustomer;

							// to avoid the loop #1028
							if( $outgoingTextCustomer != $phone ){

							$msg .= "To contact Crunchbutton, call ".c::config()->phone->support.".\n";
							$msg .= 'Or, send us a text by replying with "support" '."\n";
			
								echo '<Sms>'.$msg.'</Sms>';		
							}
						
						break;
				}
		}

		echo '</Response>';
			
		exit;
	}
}
