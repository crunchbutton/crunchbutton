<?php

class Controller_api_Support extends Crunchbutton_Controller_Rest {
	public function init() {

		switch ( $this->method() ) {
			// Saves a suggestion
			case 'post':

				if (c::getPagePiece(2) == 'sms') {
					

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
					$support->queNotify();
					echo $support->json();
				}

			break;

			case 'get':
				echo json_encode(['error' => 'invalid object']);
		}
	}
}