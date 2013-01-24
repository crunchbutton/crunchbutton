<?php

class Crunchbutton_Auth {
	private $_user;
	private $_session;

	public function __construct() {
		$this->_session = new Crunchbutton_Session;
		session_start();
		
		// here we need to check for a token
		// if we dont have a valid token, we need to check for a facebook cookie
		// then if none of thats good just return a blank user object

		if ($_COOKIE['token'] && !$this->session()->id_user) {
			$sess = Session::token($_COOKIE['token']);
			if ($sess->id_user) {
				$this->session()->id_user = $sess->id_user;
				$this->session()->token   = $_COOKIE['token'];
			} else { // if no id_user in session, delete cookie and session in DB as it's not used, see #624
				Session::deleteToken($_COOKIE['token']);
				setcookie('token','',0,'/');
			}
		}

		if ($this->session()->id_user) {
//			if ($this->session()->ip == $_SERVER['REMOTE_ADDR']) {
				$this->_user = new Crunchbutton_User($this->session()->id_user);
				$this->session()->date_active = date('Y-m-d H:i:s');
				$this->session()->save();
//			}
		} else {
			$this->_user = new Crunchbutton_User;
		}

	}

	public function doAuth($type, $id) {
		$auth = Crunchbutton_User_Auth::byTypeId($type,$id);

		if ($auth->active && $auth->user()->active) {
			$this->_user = $auth->user();
			$this->session()->id_user = $this->user()->id_user;
			$this->session()->id_user_auth = $auth->id_user_auth;
			$this->session()->save();

			return true;
		}
		return false;
	}

	public function user($user = null) {
		if ($user) $this->_user = $user;

		if (!isset($this->_user)) {
			$this->_user = new Crunchbutton_User;
		}

		return $this->_user;
	}


	public function get($var) {
		return $_SESSION[$var];
	}

	public function set($var,$value) {
		$_SESSION[$var] = $value;
	}

	public function id() {
		return $this->_session;
	}

	public function ip() {
		return $this->_ip;
	}

	public function destroy() {
		$this->_session = session_id();
		Caffeine::db()->query('UPDATE session SET active=0 WHERE session="'.$this->id().'"');
		session_regenerate_id();
		$this->_session = session_id();
		$this->_user = new Crunchbutton_User;
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
		$user->save();
	}

	public function session() {
		return $this->_session;
	}
}