<?php

class Crunchbutton_Auth {
	private $_user;
	private $_session;

	public function __construct() {
		$this->_session = new Crunchbutton_Session;
		session_start();
		
		//check for admin
		if ($_SERVER['HTTP_AUTHORIZATION']) {
			list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':' , base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
		}
		
		if ($_SERVER['PHP_AUTH_USER']) {
		
			$admin = Admin::login($_SERVER['PHP_AUTH_USER']);

			if ($admin->id_admin && sha1(c::crypt()->encrypt($_SERVER['PHP_AUTH_PW'])) == $admin->pass) {
				// we have a valid login
				c::admin($admin);
				$_SESSION['admin'] = true;
			}
		}


		// here we need to check for a token
		// if we dont have a valid token, we need to check for a facebook cookie
		// then if none of thats good just return a blank user object
		if ($_COOKIE['token'] && !$this->session()->id_user) {
			$sess = Session::token($_COOKIE['token']);
			if ($sess->id_user) {
				$token = $_COOKIE['token'];
				$data = $sess->data;
				$id_user = $sess->id_user;
				// Issue #973 - if the new id_session is different of the new one it means it is another session
				// the old session must to be deleted
				if( $this->session()->id_session != $sess->id_session ){
					$this->session()->data = $data;
					Session::deleteToken( $token );
				}
				$this->session()->id_user = $id_user;
				$this->session()->token   = $token;
			} else { // if no id_user in session, delete cookie and session in DB as it's not used, see #624
				Session::deleteToken($_COOKIE['token']);
				setcookie('token','',0,'/');
			}
		}

		// we have a successful user
		if ($this->session()->id_user) {
			// if ($this->session()->ip == $_SERVER['REMOTE_ADDR']) {
			$this->_user = new Crunchbutton_User($this->session()->id_user);
			$this->session()->date_active = date('Y-m-d H:i:s');
			$this->session()->save();
		}

		// if we dont have a user lets check for a facebook user.
		// not sure if theres any way to avoid this, but if a fb user is found, we have to make a fb request
		// which take a little bit of time
		if (!$this->_user) {

			// check for a facebook cookie
			foreach ($_COOKIE as $key => $value) {
				if (preg_match('/^fbsr_.*$/', $key)) {
				
					// we found a cookie!
					$fb = new Crunchbutton_Auth_Facebook;
					
					if ($fb->user()->id) {
						// we have a facebook user
						$user = User::facebook($fb->user()->id);
	
						if (!$user->id_user) {
							// we dont have a user, and we need to make one
							$user = new User;
							$user->active = 1;
							$user->name = $fb->user()->name;
							$user->email = $fb->user()->email;
							$user->save();
							
							$userAuth = new User_Auth;
							$userAuth->active = 1;
							$userAuth->id_user = $user->id_user;
							$userAuth->type = 'facebook';
							$userAuth->auth = $fb->user()->id;
							$userAuth->save();
						} else {
							$user = $user->get(0);
						}
						
						$this->_user = $user;
						$this->session()->id_user = $user->id_user;
						$this->session()->date_active = date('Y-m-d H:i:s');
						$this->session()->generateAndSaveToken();
						setcookie('token', $this->session()->token, (new DateTime('3000-01-01'))->getTimestamp(), '/');
	
					} else {
						// we dont have a facebook user
					}
	
					break;
				}
			}
		
		}
		
		// we still dont have a user, so just set a blan object
		if (!$this->_user) {
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

	public function doAuthByLocalUser( $params ) {
		$auth = User_Auth::localLogin( $params['email'], $params['password'] );
		if ( $auth->user()->active ) {
			$this->_user = $auth->user();			
			$this->session()->id_user = $this->user()->id_user;
			$this->session()->id_user_auth = $auth->id_user_auth;
			$this->session()->date_active = date('Y-m-d H:i:s');
			$this->session()->generateAndSaveToken();
			setcookie('token', $this->session()->token, (new DateTime('3000-01-01'))->getTimestamp(), '/');
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