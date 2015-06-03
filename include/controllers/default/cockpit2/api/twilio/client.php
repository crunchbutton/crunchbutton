<?php


//inits the client

class Controller_Api_Twilio_Client extends Crunchbutton_Controller_RestAccount {

	public function init() {
		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud'])) {
			header('HTTP/1.1 401 Unauthorized');
			exit;
		}

		switch (c::getPagePiece(2)) {
			case 'test':
				$callerId = c::config()->twilio->apps->test;
				break;
			default:
				$callerId = c::config()->twilio->apps->customer;
				break;
		}

		$capability = new Services_Twilio_Capability(c::config()->twilio->live->sid, c::config()->twilio->live->token);
		
		$capability->allowClientOutgoing($callerId);
		$capability->allowClientIncoming(c::admin()->id_admin);

		echo json_encode(['token' => $capability->generateToken()]);

	}
}