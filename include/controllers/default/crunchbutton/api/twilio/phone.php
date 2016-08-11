<?php

class Controller_api_twilio_phone extends Crunchbutton_Controller_Rest {
	public function init() {

		$_REQUEST['forward'] = preg_replace('/[^0-9]/','',$_REQUEST['forward']);
		header('Content-type: text/xml');

		switch (c::getPagePiece(3)) {
		    case 'recording':
		    case 'complete':
				echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"
					.'<Response></Response>';

		    	break;
		    case 'call':
				$callback = 'http'.($_SERVER['HTTPS'] != 'on' ? '' : 's').'://'.c::config()->host_callback.'/api/twilio/phone/recording';
				$forwardTo = Crunchbutton_Support::forwardCSCall($this->request());
				echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"
					.'<Response>'
					.'<Dial timeout="10" record="record-from-answer" trim="trim-silence" action="'.$callback.'">'.($forwardTo).'</Dial>'
					.'</Response>';
		    	break;
			case 'dial':
				$callback = 'http'.($_SERVER['HTTPS'] != 'on' ? '' : 's').'://'.c::config()->host_callback.'/api/twilio/phone/recording';
				$forwardTo = Crunchbutton_Support::forwardCSCall($this->request());
				echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"
					.'<Response>'
						.'<Say>Testing</Say>'
						.'<Dial timeout="1">9</Dial>'
						.'<Dial timeout="1">#</Dial>'
					.'</Response>';
		    	break;
		}

		$request = $this->request();
		Call::logFromTwilio($request);
		exit;
	}
}