<?php
class Controller_api_twilio_error extends Crunchbutton_Controller_Rest {

	public function init() {
			
		$env = c::env() == 'live' ? 'live' : 'dev';

		switch ( c::getPagePiece( 3 ) ) {

			case 'notification':

				$twilio = new Services_Twilio( c::config()->twilio->{ $env }->sid, c::config()->twilio->{ $env }->token );
	
				// Get the Callsid
				$CallSid = $_REQUEST[ 'CallSid' ];

				if( $CallSid ){

					$call = $twilio->account->calls->get( $CallSid );

					// Get the notifications of this call
					$notifications = $call->notifications;

					// Log
					Log::debug( [ 'action' => 'Twilio error notification', 'CallSid' => $CallSid, 'type' => 'twilio error' ] );

					// Read each notification
					foreach( $notifications as $notification ){
						$notification = $twilio->account->notifications->get($notification->sid);
						$this->processErrorMessage( $notification->message_text, $call->to );
					}
				}

				header('Content-type: text/xml');
				echo '<?xml version="1.0" encoding="UTF-8" ?>' .
							'<Response>' .
								'<Say voice="'.c::config()->twilio->voice.'">' .
									'An application error has occured . ' .
								'</Say>' .
							'</Response>';

			break;
		}
	}

	public function processErrorMessage( $error_message, $callto ){
		
		$env = c::env() == 'live' ? 'live' : 'dev';

		$twilio = new Services_Twilio( c::config()->twilio->{ $env }->sid, c::config()->twilio->{ $env }->token );

		$sms_error = 'Twilio Error!';
		$sms_error .= "\n";
		$sms_error .= "\n";

		// Phone number twilio tried to call
		$sms_error .= 'Call To: ' . $callto;
		$sms_error .= "\n";

		foreach ( explode( '&', $error_message ) as $piece ) {

			$param = explode( '=', $piece );

			if ($param) {

				$key = urldecode( $param[ 0 ] );
				$value = urldecode( $param[ 1 ] );

				switch ( $key ) {

					// Error code: http://www.twilio.com/docs/errors/reference
					case 'ErrorCode':
						$sms_error .= 'Error Code: '	. $value;
						$sms_error .= "\n";
						break;
					
					// Url twilio tried to access
					case 'url':

						// Get the id_notification_log
						if( strpos( $value, '/api/notification' ) ){
							$url = explode( '/api/notification/', $value );
							$url = $url[ 1 ];
							$url = str_replace( 'confirm', '' , $url);
							$url = str_replace( '/', '' , $url);
							$id_notification_log = $url;
							$notification = Notification_Log::o( $id_notification_log );
							if( $notification->order()->id_order ){
								$sms_error .= 'Order: '	. $notification->order()->id_order;
								$sms_error .= "\n";										
							}
						}
						
						$sms_error .= 'URL: '	. $value;
						$sms_error .= "\n";
						break;
				}
			}
		}

		// Log
		Log::debug( [ 'action' => 'Twilio error', 'CallSid' => $_REQUEST[ 'CallSid' ], 'sms_error' => $sms_error, 'error_message' => $error_message, 'type' => 'twilio error' ] );

		$message = str_split( $sms_error, 160 );

		// Send this message to the customer service
		foreach ( c::config()->text as $supportName => $supportPhone) {
			
			$num = $supportPhone;
			
			foreach ( $message as $msg ) {

				try {
					// Log
					Log::debug( [ 'CallSid' => $_REQUEST[ 'CallSid' ], 'action' => 'sending sms - twilio error', 'num' => $num, 'msg' => $msg, 'type' => 'twilio error' ] );
					$twilio->account->sms_messages->create(
						c::config()->twilio->{$env}->outgoingTextCustomer,
						'+1'.$num,
						$msg
					);
				} catch (Exception $e) {
					// Log
					Log::debug( [ 'CallSid' => $_REQUEST[ 'CallSid' ], 'action' => 'ERROR sending sms - twilio error', 'num' => $num, 'msg' => $msg, 'type' => 'twilio error' ] );
				}
			}
		}
	}
}
