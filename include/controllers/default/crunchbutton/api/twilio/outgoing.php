<?php

class Controller_api_twilio_outgoing extends Crunchbutton_Controller_Rest {
	public function init() {
	    header('Content-type: text/xml');

	    if (!$_REQUEST['PhoneNumber']) {
		    exit;
	    }

		if (preg_match('/^[\d\+\-\(\) ]+$/', $_REQUEST['PhoneNumber'])) {
			$num = '<Number>'.$_REQUEST['PhoneNumber'].'</Number>';
		} else {
			$num = '<Client>'.$_REQUEST['PhoneNumber'].'</Client>';
		}

		switch (c::getPagePiece(3)) {
			case 'restaurant':
				$callerId = c::config()->twilio->live->outgoingRestaurant;
				break;
			case 'driver':
				$callerId = c::config()->twilio->live->outgoingDriver;
				break;
			case 'test':
				$callerId = c::config()->twilio->live->outgoingTest;
				break;
			case 'customer':
			default:
				$callerId = c::config()->twilio->live->outgoingCustomer;
				break;
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"
			.'<Response>'
			.'<Pause length="10"/>'
			.'<Dial callerId="'.$callerId.'">'.$num.'</Dial>'
			.'</Response>';

		exit;
	}
}