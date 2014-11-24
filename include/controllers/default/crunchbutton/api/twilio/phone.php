<?php

class Controller_api_twilio_phone extends Crunchbutton_Controller_Rest {
	public function init() {
		
		$_REQUEST['forward'] = preg_replace('/[^0-9]/','',$_REQUEST['forward']);
	    header('Content-type: text/xml');
	    
	    $callback = 'http'.($_SERVER['HTTPS'] != 'on' ? '' : 's').'://'.$this->host_callback().'/api/twilio/phone/recording';

		echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"
			.'<Response>'
			.'<Dial timeout="10" record="record-from-answer" trim="trim-silence" action="'.$callback.'">'.($_REQUEST['forward'] ? $_REQUEST['forward'] : '800-351-4161').'</Dial>'
			.'</Response>';

		Call::logFromTwilio($_REQUEST);

		exit;
	}
}