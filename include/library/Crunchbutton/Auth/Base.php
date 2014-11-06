<?php

class Crunchbutton_Auth_Base extends Cana_Model {
	private $_user;
	private $_session;

	public function __construct() {
		$this->_session = new Crunchbutton_Session;
		session_start();
		
		$this->init();

		// here we need to check for a token
		// if we dont have a valid token, we need to check for a facebook cookie
		// then if none of thats good just return a blank user object
		if (!$this->user()->id && $_COOKIE['token'] && !$this->session()->id) {
			$sess = Session::token($_COOKIE['token']);
			$id = $sess->id_user ? $sess->id_user : $sess->id_admin;

			if ($sess->id_user || $sess->id_admin) {
				$token = $_COOKIE['token'];
				$data = $sess->data;
				$id_user = $sess->id_user;
				$id_admin = $sess->id_admin;

				// Issue #973 - if the new id_session is different of the new one it means it is another session
				// the old session must to be deleted
				$id_session = $sess->id_session;
				if ($this->session()->id_session != $sess->id_session) {
					$this->session()->data = $data;
					Session::deleteToken($token);
				}
				$this->session()->id_session = $id_session;
				$this->session()->id_user = $id_user;
				$this->session()->id_admin = $id_admin;
				
				$headers = apache_request_headers();
				if ($headers['Session-Type'] && trim($headers['Session-Type']) == 'passthru' && trim($headers['Session-Type-Key']) == c::config()->site->config('chat-server-key')->val()) {
					
				} else {
					$this->session()->token   = $token;
				}
			} else {
				// if no id_user in session, delete cookie and session in DB as it's not used, see #624
				Session::deleteToken($_COOKIE['token']);
				setcookie('token','',0,'/');
			}
		}

		// we have a successful user
		if ($this->session()->id_user || $this->session()->id_admin) {
			$this->user($this->userObject($this->session()->id_admin ? $this->session()->id_admin : $this->session()->id_user));
			$this->session()->date_active = date('Y-m-d H:i:s');
			$this->session()->save();
		}
		
		$this->postInit();


		// if we still dont have a user, so just set a empty object
		if (!$this->user()->id) {
			$this->user($this->userObject());
		}
	}
	
	public function postInit() {
		
	}
	
	public function facebook($fb = null) {
		if (isset($fb)) {
			$this->_facebook = $fb;
		}
		return $this->_facebook;
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
		$this->session()->id_user = $user->id_user;
		$this->session()->date_active = date('Y-m-d H:i:s');
		$this->session()->generateAndSaveToken();
		setcookie('token', $this->session()->token, (new DateTime('3000-01-01'))->getTimestamp(), '/');
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
		if (isset($user)) {
			$this->_user = $user;
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

	public function session($session = null) {
		if (!is_null($session)) {
			$this->_session = $session;
		}
		return $this->_session;
	}
}