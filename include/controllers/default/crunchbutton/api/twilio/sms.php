<?php
class Controller_api_twilio_sms extends Crunchbutton_Controller_Rest {
	public function init() {
		Message_Incoming_Sms::route($this->request());
	}
}