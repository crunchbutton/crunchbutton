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
				if ($body{0} == '@') {
					$id = str_replace('@','',$body);

					$rsess = new Session_Twilio($id);
					if (!$rsess->id_session_twilio) {
						$msg = 'Invalid ID. Enter a session id to reply to. ex: "@123"';
					} else {
						$msg = 'Now replying to @'.$rsess->id_session_twilio.'. Type a message to respond.';
						$_SESSION['support-respond-sess'] = $rsess->id_session_twilio;
					}

				} elseif ($_SESSION['support-respond-sess']) {
					$rsess = new Session_Twilio($_SESSION['support-respond-sess']);
					$message = $rep.': '.$body;
					$message = str_split($message,160);
					
					$nums = [$rsess->phone];
					
					foreach (c::config()->text as $supportName => $supportPhone) {
						if ($supportName == $rep) continue;
						$nums[] = $supportPhone;
					}

					$b = $message;
					c::timeout(function() use ($nums, $b, $twilio, $env) {
						foreach ($nums as $num) {
							foreach ($b as $msg) {
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
		
				if (!$_SESSION['support-order-num']) {
					$order = Order::q('select * from `order` where phone="'.$phone.'" order by date desc limit 1');
				} elseif ($_SESSION['support-order-num'] != 'none') {
					$order = Order::o($_SESSION['support-order-num']);
				} else {
					$order = null;
				}

			
				switch ($_SESSION['sms-action']) {
					case 'support-orderquestion':
						$message = strtolower($body);
						switch ($message) {
							case 'yes':
							case 'y':
							case 'ya':
							case 'yeah':
								$_SESSION['support-order-num'] = $order->id_order;
								$_SESSION['sms-action'] = 'support-ask';
								$msg = 'Great. What can we help you with?';
								break;
		
							case 'no':
							case 'nay':
							case 'n':
							case 'nop':
							case 'nope':
								$_SESSION['sms-action'] = 'support-orderspecific';
								$msg = 'OK. What is your order number? Or enter "none" if you don\'t know.';
								break;
		
							default:
								$msg = 'I didn\'t understand that. Is this about order #'.$order->id_order.'?';
								break;
								
						}
						echo '<Sms>'.$msg.'</Sms>';
						break;
		
					case 'support-orderspecific':
						$body = strtolower($body);
						if ($body == 'none') {
							$_SESSION['support-order-num'] = 'none';
							$_SESSION['sms-action'] = 'support-ask';
							$msg = 'OK. What can we help you with?';
						}
						$orderNum = preg_replace('/[^0-9]+/','',$body);
						$order = new Order($orderNum);
		
						if ($order->id_order) {
							$_SESSION['support-order-num'] = $order->id_order;
							$_SESSION['sms-action'] = 'support-ask';
							$msg = 'Great. What can we help you with?';
						} else {
							$msg = 'I couldn\'t find that order. What was it again?';				
						}
						echo '<Sms>'.$msg.'</Sms>';
						break;
		
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
						if (strtolower($body) == 'support') {
							if ($order->id_order) {
								$_SESSION['sms-action'] = 'support-orderquestion';
								$msg = 'Is this about order #'.$order->id_order.'?';
							} else {
								$_SESSION['sms-action'] = 'support-orderspecific';
								$msg = 'OK. What is your order number? Or enter "none" if you don\'t know.';
							}
		
				
						} else {
							if ($order->id_order) {
								$msg = 'To contact '.$order->restaurant()->shortName().", call ".$order->restaurant()->phone().".\n";
							}
							$msg .= "To contact Crunchbutton, call ".c::config()->phone->support.".\n";
							$msg .= 'Or, send us a text by replying with "support" '."\n";
		
						}
						echo '<Sms>'.$msg.'</Sms>';
					break;
				}
				break;

		}

		echo '</Response>';
			
		exit;
	}
}