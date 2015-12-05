<?php
class Controller_api_twilio_sms_status extends Crunchbutton_Controller_Rest {
	public function init() {
		$data = $_POST;
		if( $data && count( $data ) > 0 ){
			$log = Crunchbutton_Phone_Log::byTwilioId( $data[ 'SmsSid' ] );
			if( $log->id_phone_log ){
				$log->status = $data[ 'MessageStatus' ];
				$log->save();
				$log->emit();
			}
		}
	}
}