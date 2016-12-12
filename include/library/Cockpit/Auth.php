<?php

class Cockpit_Auth extends Crunchbutton_Auth_Base {

	public function init() {

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
				$this->user($admin);
			}
		}
	}

	public function postInit() {
		if ($this->user()->id_admin) {
			c::admin($this->user());

			$ghost = $_GET['_ghost'];
			if (!$ghost) {
				$ghost = $this->session()->adapter()->get('_ghost');
			}

			if ($this->user()->permission()->check(['global','ghost'])) {

				if ($ghost && $ghost != 'ME') {
					$u = new Admin($ghost);
					if ($u->id_admin) {
						$this->user($u);
						$this->session()->adapter()->set('_ghost',$u->id_admin);
					}
				} elseif ($ghost == 'ME') {
					$this->session()->adapter()->set('_ghost',null);
				}

			} else {
				if ($ghost) {
					$this->session()->adapter()->set('_ghost',null);
				}
			}
		} else {
			$this->user(new Admin);
		}

	}

	public function setUser($user) {
		$this->_user = $user;
		$this->session()->adapter()->id_user = $user->id_user;
		$this->session()->adapter()->date_active = date('Y-m-d H:i:s');
		$this->session()->generateAndSaveToken();
		setcookie('token', $this->session()->token, (new DateTime('3000-01-01'))->getTimestamp(), '/');
		$_COOKIE['token'] = $this->session()->token;
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

	public function doAuthByLocalUser($params) {
		$auth = Admin_Auth::localLogin($params['email'], $params['password']);
		if ($auth->active) {
			c::admin($auth);
			$this->user($auth);
			$this->session()->adapter()->id_admin = $this->user()->id_admin;
			$this->session()->adapter()->date_active = date('Y-m-d H:i:s');
			$this->session()->data = 'session: ';
			if(!$this->session()->session_id){
				$this->session()->session_id = session_id();
				$this->session()->data .= $this->session()->session_id;
			}
			$this->session()->generateAndSaveToken();
			if (!headers_sent()) {
				setcookie('token', $this->session()->token, (new DateTime('3000-01-01'))->getTimestamp(), '/');
				$_COOKIE['token'] = $this->session()->token;
				// app
				$headers = apache_request_headers();
				if ($headers['App-Version'] && $this->session()->token) {
					header('App-Token: '.$this->session()->token);
				}
			}
			return true;
		}
		return false;
	}

	public function user($user = null) {
		if (isset($user)) {
			$this->_user = $user;
		} elseif (!isset($this->_user)) {
			$this->_user = $this->userObject();
		}

		return $this->_user;
	}

	public function userObject($params = null) {
		if ($params) {
			return new Crunchbutton_Admin($params);
		} else {
			return new Crunchbutton_Admin;
		}
	}

}
