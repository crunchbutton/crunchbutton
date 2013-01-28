<?php

class Crunchbutton_Auth_Facebook extends Cana_Model {
	public function __construct($data = null) {
		if (!$data) {
			$this->check();
		} else {
			$this->_user = $data;
		}
	}

	public function check() {

		$this->_facebook = new Cana_Facebook([
			'appId'	=> Cana::config()->facebook->app,
			'secret' => Cana::config()->facebook->secret
		]);

		$user = $this->_facebook->getUser();

		if ($user) {
			try {
				$userObject = $this->_facebook->api('/'.$user);
			} catch (Cana_Facebook_Exception $e) {
				// debug for now
				$userObject = null;
			}
		}

		$this->_user = Cana_Model::toModel($userObject);
		return $this;
	}

	public function login() {
		header('Location: '.$this->_facebook->getLoginUrl().'&scope=email');	
		exit;
	}

	public function logout() {
		header('Location: '.$this->_facebook->getLogoutUrl());
		exit;
	}

	public function user() {
		return $this->_user;
	}

	public function facebook() {
		return $this->_facebook;
	}
}