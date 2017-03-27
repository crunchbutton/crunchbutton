<?php

class Controller_api_download extends Crunchbutton_Controller_Rest {
	public function init() {
		$input = $_REQUEST['num'];

		if (!$input) {
			$input == c::user()->phone;
		}

		// trim whitespace
		$num = trim($input);

		// get rid of non numbers
		$num = preg_replace('/[^\d]/','',$input);

		// trincate
		$num = substr($num, 0, 10);

		// remove 0 and 1 starters
		$num = preg_replace('/^0|^1/','',$num);

		if ($num != $input || !$num) {
			echo json_encode(['error' => 'invalid phone number', 'status' => false]);
			exit;
		}

		Crunchbutton_Message_Sms::send([
			'to' => $num,
			'message' => "YAY! Crunchbutton for mobile!\nhttp://crunchbutton.com/app",
			'reason' => Crunchbutton_Message_Sms::REASON_APP_DOWNLOAD
		]);

		echo json_encode(['status' => true]);

		exit;
	}
}