<?php

class Controller_api_twilio_outgoing extends Crunchbutton_Controller_Rest {
	public function init() {
	    header('Content-type: text/xml');
		echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"
			.'<Response>'
			.'<Dial callerId="'.c::config()->twilio->live->outgoingCustomer.'"><Number>'.($_REQUEST['PhoneNumber'] ? $_REQUEST['PhoneNumber'] : '800-351-4161').'</Number></Dial>'
			.'</Response>';
			
		exit;
	}
}