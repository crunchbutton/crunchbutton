<?php

class Controller_api_login extends Crunchbutton_Controller_Rest {
	public function init() {
		$user = c::auth()->doAuthByLocalUser(['email' => $this->request()['username'], 'password' => $this->request()['password']]);
		if ($user) {
			echo c::admin()->json();
		} else {
			echo json_encode(['error' => 'invalid login']);
		}
	}
}