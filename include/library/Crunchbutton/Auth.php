<?php

class Crunchbutton_Auth extends Crunchbutton_Auth_Base {

	public function init() {

	}

	public function postInit() {
		// if we dont have a user lets check for a facebook user.
		// not sure if theres any way to avoid this, but if a fb user is found, we have to make a fb request
		// which take a little bit of time
		if (!$this->user()->id) {
			// check for a facebook cookie
			foreach ($_COOKIE as $key => $value) {
				if (preg_match('/^fbsr_.*$/', $key)) {
					// we found a cookie! perform a facebook authentication via the api
					$this->_facebook = new Crunchbutton_Auth_Facebook;
					$this->fbauth();
					break;
				}
			}
		}
	}

	public function fbauth() {
		// we have a facebook user
		if ($this->facebook()->fbuser()->id) {
			$createNewUser = ( $this->user()->id_user ) ? false : true;
			$user = User::facebookCreate($this->facebook()->fbuser()->id, $createNewUser);
			if ($user) {
				$this->setUser($user);
			}
		}
		return $this;
	}

	public function setUser($user) {
		$this->_user = $user;
		$this->session()->adapter()->id_user = $user->id_user;
		$this->session()->adapter()->date_active = date('Y-m-d H:i:s');
		$this->session()->generateAndSaveToken();
		setcookie('token', $this->session()->token, (new DateTime('3000-01-01'))->getTimestamp(), '/');
		// app
		$headers = apache_request_headers();
		if ($headers['App-Version'] && $this->session()->token) {
			header('App-Token: '.$this->session()->token);
		}
	}

	public function doAuth($type, $id) {
		$auth = Crunchbutton_User_Auth::byTypeId($type,$id);
		if ($auth->active && $auth->user()->active) {
			$this->_user = $auth->user();
			$this->session()->adapter()->id_user = $this->user()->id_user;
			$this->session()->adapter()->id_user_auth = $auth->id_user_auth;
			$this->session()->adapter()->save();
			return true;
		}
		return false;
	}

	public function doAuthByLocalUser( $params ) {
		$auth = User_Auth::localLogin( $params['email'], $params['password'] );
		if ( $auth->user()->active ) {
			$this->_user = $auth->user();
			error_log(' -- user: '. $this->user()->id_user);
			error_log(' -- auth: '. $auth->id_user_auth);
			error_log(' -- other user: '. $this->_user);
			$this->session()->adapter()->id_user = $this->user()->id_user;
			$this->session()->adapter()->id_user_auth = $auth->id_user_auth;
			$this->session()->adapter()->date_active = date('Y-m-d H:i:s');
			$this->session()->generateAndSaveToken();
			setcookie('token', $this->session()->token, (new DateTime('3000-01-01'))->getTimestamp(), '/');
			// app
			$headers = apache_request_headers();
			if ($headers['App-Version'] && $this->session()->token) {
				header('App-Token: '.$this->session()->token);
			}
			return true;
		}
		return false;
	}

	public function user($user = null) {
		if (isset($user)) {
			$this->_user = $user;
		} elseif (!isset($this->_user)) {
			$this->_user = new Crunchbutton_User;
		}

		return $this->_user;
	}

	public static function load($user, $profile) {
		$user->permalink = $profile->username ? $profile->username : $profile->id;
		$user->fbid = $profile->id;
		$user->first_name = $profile->first_name;
		$user->last_name = $profile->last_name;
		$user->birthday = $profile->birthday;
		$user->email = $profile->email;
		$user->locale = $profile->locale;
		$user->gender = $profile->gender;
		$user->timezone = $profile->timezone;
		$user->saving_from = $user->saving_from.'Auth::load - ';
		$user->save();
	}

	public function userObject($params = null) {
		if ($params) {
			return new Crunchbutton_User($params);
		} else {
			return new Crunchbutton_User;
		}
	}
}
