<?php

class Crunchbutton_Session {

	public function __construct() {
		/*
		c::config()->session = (object)[
			'type' => 'redis',
			'url' => 'tcp://redis-1.crunchbutton.arzynik.cont.tutum.io:6379?4O3pdA0UEJhPXKEnUCvl9pJC1cYVsCrc'
		];
		*/
		if (c::app()->cli || c::app()->ignoreSession) {
			// if we are in cron or on a rest endpoint that doesnt need auth (twilio)
			$this->adapter(new Crunchbutton_Session_Adapter_Cli);
		} elseif (c::config()->session->type == redis) {
			// @todo
			$this->adapter(new Crunchbutton_Session_Adapter_Redis(c::config()->session->url));
		} else {
			// mysql
			$this->adapter(new Crunchbutton_Session_Adapter_Sql);
		}
	}

	public function generateAndSaveToken() {
		if ($this->adapter()->generateAndSaveToken()) {
			$this->token = $this->adapter->token;
			return true;
		}

		// only do this if the adapter doesnt support its own token handling
		if (($this->adapter()->id_user || $this->adapter()->id_admin) && !$this->adapter()->token) {
			$fields = '-=d4sh0fs4|t?&4ndM4YB350m35ymb0||0v3!!!!!!=-' . $this->adapter()->id_session . $this->adapter()->id_user . $this->adapter()->id_admin . uniqid();
			$token = new Crunchbutton_Session_Token;
			$token->id_session = $this->id_session;
			$token->id_user = $this->id_user;
			$token->id_admin = $this->id_admin;
			$token->token = strtoupper(hash('sha512', $fields));
			$token->save();
			$this->token = $token->token;
		}
	}

	public static function deleteToken($token) {
		if (!$token) return false;
		Cana::db()->query('delete from session where token=?',[$token]);
	}

	public function adapter($adapter = null) {
		if (!is_null($adapter)) {
			$this->_adapter = $adapter;
		}
		return $this->_adapter;
	}

	public static function token($token = null) {
		if (!$token) return false;

		$res = Cana::db()->query('select * from session where token=?', [$token]);
		$session = $res->fetch();
		//$session->closeCursor();

		if ($session->id_session) {
			return $session;
		} else {
			return false;
		}
	}
}
