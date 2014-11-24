<?php

class Controller_api_twilio_phone extends Crunchbutton_Controller_Rest {
	public function init() {
		
		if (!$_REQUEST['noforward']) {
			$_REQUEST['forward'] = preg_replace('/[^0-9]/','',$_REQUEST['forward']);
		    header('Content-type: text/xml');
			echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"
				.'<Response>'
				.'<Dial timeout="10" record="false">'.($_REQUEST['forward'] ? $_REQUEST['forward'] : '800-351-4161').'</Dial>'
				.'</Response>';
		} else {
			Call::createFromTwilio($_REQUEST);
		}
			

			
		exit;
	}
}