<?php

class Controller_api_Support extends Crunchbutton_Controller_Rest {
	public function init() {

		switch ( $this->method() ) {
			// Saves a suggestion
			case 'post':

				switch ( c::getPagePiece(2) ) {
					case 'sms':
						// Create a twilio session
						$phone = str_replace( '-', '', $this->request()['phone']);;
						$phone = str_replace( ' ', '', $phone);
						$phone = str_replace( '.', '', $phone);

						$twilio_session = Session_Twilio::sessionByPhone( $phone );

						if( !$twilio_session->id_session_twilio ){
							$twilio_session = new Session_Twilio;
						}
						$twilio_session->phone = $phone;
						$twilio_session->data = json_encode( $_REQUEST );
						$twilio_session->save();

						$support = Support::getByTwilioSessionId( $twilio_session->id_session_twilio );
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
							$support = Crunchbutton_Support::createNewBoxTicket(  [ 'phone' => $phone,
																																			'name' => $this->request()['name'],
																																			'body' => $this->request()['message'],
																																			'id_session_twilio' => $twilio_session->id_session_twilio ] );
						} else {
							if( $support->status == Crunchbutton_Support::STATUS_CLOSED ){
								$support->status = Crunchbutton_Support::STATUS_OPEN;
								$support->addSystemMessage( 'Ticket reopened by customer' );
							}
							$support->addCustomerMessage( [ 'name' => $this->request()['name'],
																							'phone' => $phone,
																							'body' => $this->request()['message'] ] );
							$support->save();
						}

						$newMessageNotification =
								'New support ticket (Help box) @'.$support->id_support."\n".
								'C: '.$this->request()['name'].' / '.$phone."\n".
								'M: '.$this->request()['message'];
							Crunchbutton_Message_Incoming_Support::notifyReps($newMessageNotification, $support);

						echo $support->json();
						$support->notify( false );
					break;
					case 'run-business':
						$this->runBusiness();
						break;
					case 'say':
						$this->say();
						break;
				}

			break;

			case 'get':

				switch ( c::getPagePiece( 2 ) ) {
					case 'say':
						$this->say();
						break;
					default:
						echo json_encode(['error' => 'invalid object']);
						break;
				}
		}
	}

	private function runBusiness(){
		$name = $this->request()['name'];
		$email = $this->request()['email'];
		$phone = $this->request()['phone'];
		$address = $this->request()['address'];
		$reason = $this->request()['reason'];

		$html = 'Name: ' . $name . '<br>';
		$html .= 'Email: ' . $email . '<br>';
		$html .= 'Phone: ' . $phone . '<br>';
		$html .= 'Address: ' . $address . '<br>';
		$html .= 'Why: ' . $reason;

		$res = c::mailgun()->sendMessage(c::config()->mailgun->domain, [
		'from' => 'iwanttobuildmyownbusiness@_DOMAIN_',
		'to' => 'iwanttobuildmyownbusiness@_DOMAIN_',
		'subject'	=> 'I want to build my own business',
		'html' => $html


		]);

	}

	function say(){

		$id_support = c::getPagePiece( 3 );

		Log::debug( [ 'action' => 'Calling', 'id_support' => $id_support, 'type' => 'support' ] );

		$support = Crunchbutton_Support::o( $id_support );

		$message = ' . You received a new support sms . ';

		if( $support->id_support ){

			if( is_numeric( $support->name ) ){
				$name = implode( ' . ', str_split( $support->name ) );
			} else {
				$name = $support->name;
			}

			$phone = implode( ' . ', str_split( $support->phone ) );

			$message .= ' . Name . ' . $name . ' . ';
			$message .= ' . Phone . ' . $phone . ' . ';
			$message .= ' . Message . ' . $support->message . ' . ';
		}

		$supportName = c::getPagePiece( 4 );

		$message .= ' . ';

		Log::debug( [ 'action' => 'Calling', 'said' => $message, 'supportName' => $supportName, 'type' => 'support' ] );

		header('Content-type: text/xml');
		echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
					.'<Response>' . "\n"
						.'<Say voice="' . c::config()->twilio->voice . '">' . "\n"
							. 'Hello ' . $supportName . ' . , ' . "\n"
							. $message . "\n"
						.'</Say>' . "\n"
					.'</Response>';
	}

}