<?php

class Crunchbutton_Message_Incoming_Response extends Cana_Model {
	public static function twilioSms($response, $from) {
		header('Content-type: text/xml');

		if ($response) {
			foreach ($response as $res) {
				if ($res->msg) {
					$sms .= '<SMS from="'.Phone::dirty($from).'">' . $res->msg . '</SMS>';
				}
			}
		}
		if( $sms ){
			echo '<?xml version="1.0" encoding="UTF-8"?>'."\n" .'<Response>' . $sms . '</Response>';
		}
	}
}