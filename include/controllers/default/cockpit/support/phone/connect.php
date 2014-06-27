<?php

class Controller_Support_Phone_Connect extends Crunchbutton_Controller_Account {

	public function init() {

		switch (c::getPagePiece(3)) {
			case 'restaurant':
				$callerId = c::config()->twilio->live->outgoingRestaurant;
				break;
			case 'driver':
				$callerId = c::config()->twilio->live->outgoingDriver;
				break;
			case 'customer':
			default:
				$callerId = c::config()->twilio->live->outgoingCustomer;
				break;
		}

		$num = c::admin()->phone;
		$host = 'beta._DOMAIN_';
		//c::config()->host_callback

		$twilio = new Services_Twilio(c::config()->twilio->live->sid, c::config()->twilio->live->token);
		$call = $twilio->account->calls->create(
			$callerId,
			'+1'.$num,
			'http://'.$host.'/api/twilio/outgoing/'.c::getPagePiece(3).'?PhoneNumber='.$_REQUEST['phone']
		);


		Log::debug( [ 'from' => $num, 'to' => $_REQUEST['phone'], 'caller' => c::getPagePiece(3), 'callerId' => $callerId,  'type' => 'connect-call' ] );

		die('pick up your phone...');
		exit;
	}
}

