<?php
class Controller_api_twilio_sms extends Crunchbutton_Controller_Rest {

	public function init() {

		$phone = str_replace('+1','',$_REQUEST['From']);
		$body = trim($_REQUEST['Body']);
		$env = c::getEnv();
		$twilio = new Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);

		$tsess = Session_Twilio::get( $_REQUEST );
		$tsess->data = json_encode( $_REQUEST );
		$tsess->save();
		$to = str_replace( '+1','',$_REQUEST[ 'To' ] );

		// Log
		Log::debug( [ 'action' => 'sms received', 'from' => $phone, 'to' => $to, 'body' => $body, 'type' => 'sms' ] );

		header('Content-type: text/xml');
		echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"
			.'<Response>';

		$sendSMSTo = array();
		foreach ( Crunchbutton_Support::getUsers() as $supportName => $supportPhone ) {
			$sendSMSTo[ $supportName ] = $supportPhone;
		}

		$admin = Admin::getCSAdminByPhone( $phone );

		if( $admin->id_admin ){
			$rep = $admin;
			$type = 'rep';
			Log::debug( [ 'action' => 'rep valid', 'rep' => $admin->name, 'rep phone' => $admin->phone, 'type' => 'sms' ] );
		}

		switch ( $type ) {

			case 'rep':

				foreach ( $sendSMSTo as $supportName => $supportPhone) {
					if ($supportPhone == $phone) continue;
					$nums[] = $supportPhone;
				}

				// Rep added a session manually
				if ( $body{0} == '@' ) {
					$id = str_replace('@','',$body);
					$rsess = new Session_Twilio($id);
					if (!$rsess->id_session_twilio) {
						$msg = 'Invalid ID. Enter a session id to reply to. ex: "@123"';
						$nums = [];
						// Log
						Log::debug( [ 'action' => 'invalid session', 'type' => 'sms' ] );
					} else {
						$_SESSION['support-respond-sess'] = $rsess->id_session_twilio;
						$atId = '@' . $rsess->id_session_twilio;
						$body = trim( str_replace( $atId , '',  $body ) );
						if( $body != '' ){
							$this->reply( $rsess->id_session_twilio, $phone, $body );
						} else {
							$msg = $rep->name . " is now replying to @".$rsess->id_session_twilio.'. Type a message to respond.';
						}
					}
				}
				// Session already exists
				elseif ($_SESSION['support-respond-sess']) {
					$this->reply( $_SESSION['support-respond-sess'], $phone, $body );
				}
				// No session at all
				else {
					$msg = 'Invalid ID. Enter a session id to reply to. ex: "@123"';
				}

				if( $msg ){
					echo '<Sms>'.$msg.'</Sms>';
				}

				break;

			// It means the message came from a customer
			default:

				$_SESSION['sms-action'] = 'support-ask';

				Log::debug( [ 'action' => 'returning sms', 'msg' => $body, 'type' => 'sms' ] );

				if (!$_SESSION['support-order-num']) {
					$order = Order::q('select * from `order` where phone="'.$phone.'" order by date desc limit 1');
				} elseif ($_SESSION['support-order-num'] != 'none') {
					$order = Order::o($_SESSION['support-order-num']);
				} else {
					$order = null;
				}

				if( $order->id_order ) {

					$restaurant = new Restaurant($order->id_restaurant);
					// hard-coding eastern daylight time because that's where
					// all our support is right now. we should think of a solution
					// to this and change it eventually. also right now our db times
					// are all pst. we should change that too.
					$types = $restaurant->notification_types();

					if( count( $types ) > 0 ){
						$notifications = '/ RN: ' . join( '/', $types );
					} else {
						$notifications = '';
					}

					$date = $order->date();
					// #2439
					$date->setTimezone( new DateTimeZone( c::config()->timezone ) );
					$date = $date->format( 'D, M d, g:i a T' );

					$community = $restaurant->communityNames();
					if( $community != '' ){
						$community = '(' . $community . ')';
					}

					$last_cb .= " Last Order: #$order->id_order, from $restaurant->name $community, on $date -  R: $restaurant->phone {$notifications} - C: $order->name / $order->phone ";
					if( $order->address ){
						$last_cb .= " / " . $order->address;
					}
				} else {
					$last_cb = ' Last Order: None.';
				}

				switch ($_SESSION['sms-action']) {

					case 'support-ask':

						if( trim( $phone ) != '' ){

							$tsess->id_order = $order ? $order->id_order : null;
							$tsess->phone = $phone;
							$tsess->save();

							$message = '@'.$tsess->id_session_twilio.' ';
							if ($order->id_order) {
								$message .= ' #'.$order->id_order.' '.$order->name.': ';
							} else {
								$message .= ': ';
							}
							$message .= htmlspecialchars($body);

							$support = Support::getByTwilioSessionId( $tsess->id_session_twilio );

							$createNewTicket = false;

							// if a user send a new message a day later, make sure it creates a new issue - #2453
							if( $support->id_support ){
								$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
								$support_date = $support->date()->get(0);
								if( $support_date ){
									$interval = $now->diff( $support_date );
									$seconds = ( $interval->s ) + ( $interval->i * 60 ) + ( $interval->h * 60 * 60 ) + ( $interval->d * 60 * 60 * 24 ) + ( $interval->m * 60 * 60 * 24 * 30 ) + ( $interval->y * 60 * 60 * 24 * 365 );
									// This ticket is too old - create a new one
									if( $seconds >= 86400 ){
										$createNewTicket = true;
									}
								} else {
									$createNewTicket = true;
								}
							} else {
								$createNewTicket = true;
							}

							if( $createNewTicket ) {
								// Create a new sms ticket
								$support = Crunchbutton_Support::createNewSMSTicket(  [ 'phone' => $phone,
																																				'id_order' => $order->id_order,
																																				'body' => $body,
																																				'id_session_twilio' => $tsess->id_session_twilio ] );
							} else {
								if( $support->status == Crunchbutton_Support::STATUS_CLOSED ){
									// Open support
									$support->status = Crunchbutton_Support::STATUS_OPEN;
									$support->addSystemMessage( 'Ticket reopened by customer' );
								}
								// Add the new customer message
								$support->addCustomerMessage( [ 'name' => $order->name,
																								'phone' => $phone,
																								'body' => $body ] );
								$support->save();
							}

							$support->makeACall();

							if(!$_SESSION['last_cb']) {
								$_SESSION['last_cb'] = $last_cb;
								$message .= "\n\n".$last_cb;
							}


							if( $support->id_support ){
								// #3666
								//$message [] = '@'.$tsess->id_session_twilio.'  http://cbtn.io/support/' . $support->id_support . '?r=1';
							}

							// Log
							Log::debug( [ 'action' => 'sms action - support-ask', 'message' => $message, 'type' => 'sms' ] );


							$sendSMSTo = array();
							foreach ( Crunchbutton_Support::getUsers() as $supportName => $supportPhone ) {
								$sendSMSTo[ $supportName ] = $supportPhone;
							}

							if( $restaurant && $restaurant->id_restaurant ){
								$usersToReceiveSMS = $restaurant->adminReceiveSupportSMS();
								if( count( $usersToReceiveSMS ) > 0 ){
									foreach( $usersToReceiveSMS as $user ){
										$sendSMSTo[ $user->name ] = $user->txt;
									}
								}
							}

							Crunchbutton_Message_Sms::send([
								'to' => $sendSMSTo,
								'message' => $message
							]);

						}
					break;

					default:

							$outgoingTextCustomer = c::config()->twilio->{$env}->outgoingTextCustomer;

							// to avoid the loop #1028
							if( $outgoingTextCustomer != $phone ){

								$msg .= "To contact Crunchbutton, call ".c::config()->phone->support.".\n";
								$msg .= 'Or, send us a text by replying with "support" '."\n";

								// Log
								Log::debug( [ 'action' => 'message to phone', 'phone' => $phone, 'msg' => $msg, 'type' => 'sms' ] );

								echo '<Sms>'.$msg.'</Sms>';
							} else {
								// Log
								Log::debug( [ 'action' => 'message to outgoingTextCustomer - ignored', 'outgoingTextCustomer' => $outgoingTextCustomer, 'type' => 'sms' ] );
							}

						break;
				}
		}

		echo '</Response>';

		exit;
	}

	public function warningOtherReps( $admin, $message ){

		$sendSMSTo = array();
		foreach ( Crunchbutton_Support::getUsers() as $supportName => $supportPhone ) {
			$sendSMSTo[ $supportName ] = $supportPhone;
		}

		if( $support->id_order && $support->order()->id_order ){
			$usersToReceiveSMS = $support->order()->restaurant()->adminReceiveSupportSMS();
			if( count( $usersToReceiveSMS ) > 0 ){
				foreach( $usersToReceiveSMS as $user ){
					$sendSMSTo[ $user->name ] = $user->txt;
				}
			}
		}

		Crunchbutton_Message_Sms::send([
			'to' => $sendSMSTo,
			'message' => $message
		]);

	}

	public function reply( $id_session, $phone, $body ){

		$rsess = new Session_Twilio( $id_session );
		$message = $body;
		$nums[] = $rsess->phone;
		$env = c::getEnv();
		$admin = Crunchbutton_Admin::getByPhone( $phone );
		// Log
		Log::debug( [ 'action' => 'replying message', 'admin' => $admin->id_admin, 'session id' => $rsess->id_session_twilio, 'num' => $nums, 'message' => $message, 'type' => 'sms' ] );

		$support = Crunchbutton_Support::getByTwilioSessionId( $id_session );
		$twilio = new Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);

		// Get the first word of the message -> action
		$action = explode( ' ',trim( $body ) );
		$action = $action[ 0 ];

		switch ( $action ) {

			// close action
			case 'close':
			case 'closed':

				if( $support->id_support ){
					$support->addSystemMessage( $admin->name . ' closed the message from text message.' );
					$support->status = Crunchbutton_Support::STATUS_CLOSED;
					$support->save();
					Log::debug( [ 'action' => 'closing support', 'id_support' => $support->id_support, 'phone' => $phone, 'message' => $message, 'type' => 'sms' ] );
					$message = $admin->name . ' closed @' . $id_session . ' / ticket #' . $support->id_support;
					$this->warningOtherReps( $admin, $message );
				}

				break;

			// just reply
			default:

				// Record the admin's message
				if( $support->id_support ){
					$support->addAdminMessage( [ 'phone' => $phone, 'body' => $body ] );
					Log::debug( [ 'action' => 'saving the answer', 'id_support' => $support->id_support, 'phone' => $phone, 'message' => $message, 'type' => 'sms' ] );
				}

				Crunchbutton_Message_Sms::send([
					'to' => $nums,
					'message' => $message
				]);

				$message = $admin->name . ' replied @' . $id_session . ' : ' . $body;
				$this->warningOtherReps( $admin, $message );
				break;
		}
	}
}
