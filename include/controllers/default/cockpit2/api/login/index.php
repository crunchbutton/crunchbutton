<?php

class Controller_api_login extends Crunchbutton_Controller_Rest {
	public function init() {
		$user = c::auth()->doAuthByLocalUser(['email' => $this->request()['user'], 'password' => $this->request()['password']]);

		if ($user) {
die('asd');
			echo c::admin()->json();
		} else {
			echo json_encode(['error' => 'invalid login']);
		}
	}
}