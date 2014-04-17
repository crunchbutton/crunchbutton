<?php

class Controller_api_twilio_phone extends Crunchbutton_Controller_Rest {
	public function init() {
	    header('Content-type: text/xml');
		echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"
			.'<Response>'
			.'<Dial timeout="10" record="false">310-935-1519</Dial>'
			.'</Response>';
			
		exit;
	}
}