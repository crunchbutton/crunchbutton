<?php

class Controller_api_user extends Crunchbutton_Controller_Rest {
	public function init() {
		// set these to debug shit for now
		Cana::config()->facebook->appId = '479805915398705';
		Cana::config()->facebook->secret = '0c3c8b3cc5b1ee36fa6726d53663a576';
		
		
		// check for a facebook cookie
		foreach ($_COOKIE as $key => $value) {
			if (preg_match('/^fbsr_.*$/', $key)) {
			
				// we found a cookie!
				$fb = new Crunchbutton_Auth_Facebook;
				
				if ($fb->user()->id) {
					// we have a facebook user
					$user = User::facebook($fb->user()->id);

					if ($user->id_user) {
						// we have a user. what should we do here?
						// should we already have this authenticated in the Auth php? problably. in fact most of this
						// code should already have been called in the Auth class. not this controller.

					} else {
						// we dont have a user, and we need to make one
						$user = new User;
						$user->name = $fb->user()->name;
						$user->email = $fb->user()->email;
						$user->save();
						c::auth()->_user = $user;

					}

					echo c::user()->json();
					exit;

				} else {
					// we dont have a facebook user
					echo c::user()->json();
				}

				break;
			}
		}

		switch ($this->method()) {
			case 'get':
				echo c::user()->json();
				break;
		}
	}
}