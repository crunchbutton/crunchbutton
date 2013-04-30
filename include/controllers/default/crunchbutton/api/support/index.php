<?php

class Controller_api_Support extends Crunchbutton_Controller_Rest {
	public function init() {

		switch ( $this->method() ) {
			// Saves a suggestion
			case 'post':

				switch ( c::getPagePiece(2) ) {
					case 'sms':
						// Create a twilio session
						$tsess = new Session_Twilio;
						$tsess->phone = $this->request()['phone'];
						$tsess->data = json_encode( $_REQUEST );
						$tsess->save();

						$support = new Crunchbutton_Support;
						$support->type = Crunchbutton_Support::TYPE_BOX_NEED_HELP;
						$support->name = $this->request()['name'];
						$support->phone = $this->request()['phone'];
						$support->message = $this->request()['message'];
						$support->ip = $_SERVER['REMOTE_ADDR'];
						$support->id_session_twilio = $tsess->id_session_twilio;
						$support->date = date('Y-m-d H:i:s');
						if( c::user()->id_user ){
							$support->id_user = c::user()->id_user;	
						}
						$support->save();
						echo $support->json();
						$support->queNotify();

					break;
					case 'say':

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
						
						break;
				}

			break;

			case 'get':

				switch ( c::getPagePiece( 2 ) ) {					
					default:
						echo json_encode(['error' => 'invalid object']);
						break;
				}
		}
	}
}