<?php

class Controller_api_Support extends Crunchbutton_Controller_Rest {
	public function init() {

		switch ( $this->method() ) {
			// Saves a suggestion
			case 'post':

				if (c::getPagePiece(2) == 'sms') {
				
					$env = c::env() == 'live' ? 'live' : 'dev';
					$phones = c::config()->suggestion->{$env}->phone;
					$twilio = new Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);

					$name = $_POST[ 'name' ];
					$phone = $_POST[ 'phone' ];
					$message = $_POST[ 'message' ];

					$message =
						"(support-" . $env . "): ".
						$name.
						"\n\n".
						"phone: ".
						$phone.
						"\n\n".
						$message;

					$message = str_split($message, 160);

					$phones = c::config()->support->{$env}->phone;

					foreach ($message as $msg) {
						foreach ($phones as $phone) {
							$twilio->account->sms_messages->create(
								c::config()->twilio->{$env}->outgoingTextCustomer,
								'+1'.$phone,
								$msg
							);
							continue;	
						}
					}

					echo json_encode(['success' => 'success']);
				}

			break;

			case 'get':
				echo json_encode(['error' => 'invalid object']);
		}
	}
}