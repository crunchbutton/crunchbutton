<?php

class Controller_Support_Phone extends Crunchbutton_Controller_Account {

	public function init() {
	

		switch (c::getPagePiece(2)) {
			case 'restaurant':
				$callerId = c::config()->twilio->apps->restaurant;
				break;
			case 'driver':
				$callerId = c::config()->twilio->apps->driver;
				break;
			case 'customer':
			default:
				$callerId = c::config()->twilio->apps->customer;
				break;
		}
		
		$capability = new Services_Twilio_Capability(c::config()->twilio->live->sid, c::config()->twilio->live->token);
		
		$capability->allowClientOutgoing('_KEY_');
		$capability->allowClientIncoming(c::admin()->firstName());
		
		c::view()->token = $capability->generateToken();
		c::view()->phone = $_REQUEST['phone'];

		c::view()->layout('layout/twilio');
		c::view()->display('twilio/outgoing');

	}
}

