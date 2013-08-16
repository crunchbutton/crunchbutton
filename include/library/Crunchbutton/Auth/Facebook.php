<?php

class Crunchbutton_Auth_Facebook extends Cana_Model {
	public function __construct($token = null, $user = null) {
		$this->_facebook = c::facebook();

		if ($token) {
			$this->facebook()->setAccessToken($token);
		}

		if (!$user) {
			$this->check();
		} else {
			$this->_user = $user;
		}
		
	}

	public function check() {
		$user = c::facebook()->getUser();

		if ($user) {
			try {
				$userObject = $this->facebook()->api('/'.$user);
			} catch (Cana_Facebook_Exception $e) {
				$userObject = null;
			}
		}

		$this->_fbuser = Cana_Model::toModel($userObject);
		return $this;
	}

	public function login() {
		header('Location: '.$this->facebook()->getLoginUrl().'&scope=email');	
		exit;
	}

	public function logout() {
		header('Location: '.$this->facebook()->getLogoutUrl());
		exit;
	}

	public function fbuser() {
		return $this->_fbuser;
	}
	
	public function user() {
		return $this->_user;
	}

	public function facebook() {
		return $this->_facebook;
	}
}