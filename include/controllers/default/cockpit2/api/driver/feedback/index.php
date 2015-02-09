<?php

class Controller_api_driver_feedback extends Crunchbutton_Controller_RestAccount {
	
	public function init() {
		$mail = new Crunchbutton_Email_Feedback([
			'name' => $this->request()['name'],
			'community' => $this->request()['community'],
			'message' => $this->request()['message']
		]);
		$mail->send();
	}
}
