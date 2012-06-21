<?php

class Crunchbutton_Auth_Local extends Cana_Model {

	public function __construct($cache = false) {

		if ($cache) {
			switch ($_SERVER['__HTTP_HOST']) {
				case 'localhost':
				case '10.0.0.8':
				case 'arzy.ath.cx':
				case 'beta.charitycall.cc':
				case 'beta.charitycall.com':
				case 'alpha.charitycall.cc':
				case 'alpha.charitycall.com':
					break;
				default:
					ini_set('session.cache_expire','10800');
					session_cache_limiter('public');
					break;
			}
		}
		session_start();

		header('Content-Type: text/html; charset=UTF-8');

		$this->_session 	= session_id();
		$this->_ip 			= $_SERVER['REMOTE_ADDR'];

		$query = '
			SELECT l.*, s.ip FROM login l
			INNER JOIN session s ON s.login_id=l.id
			WHERE s.session="'.$this->id().'"
			AND s.active=1
		';

		$result = Caffeine::db()->query($query);
		$row = $result->fetch();

		if (!empty($row->id)) {
			if ($this->ip() != $row->ip || !$row->active) {
				$this->destroy();
				return;
			}

			$this->_user = new Charitycall_Login($row->id);

			Caffeine::db()->query('UPDATE session SET date=NOW() WHERE session="'.$this->id().'"');

		} else {

			$this->_user = new Charitycall_Login;
		}
	}
	
	public function check($user, $pass) {
		$user = Caffeine::db()->escape(trim($user));
		$pass = trim($pass);
	
		$query = '
			SELECT * 
			FROM login 
			WHERE login="'.$user.'"
			AND active=1
		';
		$result = Caffeine::db()->query($query);
		$row = $result->fetch();

		if (!empty($row->login) && $pass == Caffeine::app()->crypt()->decrypt($row->pass)) {
			return $row;
		} else {
			return false;
		}		
	}
	
	public function setPass($user = null, $pass) {
		$pass = Caffeine::db()->escape(trim($pass));
		$user = !is_null($user) ? $user : $this->user();
		
		$query = '
			UPDATE login
			SET pass="'.Caffeine::app()->crypt()->encrypt($pass).'"
			WHERE id="'.$user->id.'"
		';
		Caffeine::db()->query($query);
	}


	public function login($user,$pass) {
		
		if ($row = $this->check($user,$pass)) {
			$user = Caffeine::db()->escape(trim($user));
			$pass = trim($pass);
		
			$query = '
				SELECT * FROM session
				WHERE session="'.$this->id().'"
			';
			$result = Caffeine::db()->query($query);
			$row2 = $result->fetch();

			if ($this->user()->id || isset($row2->id)) {
				$this->destroy();
			}

			$this->_user = new Charitycall_Login($row->id);

			Caffeine::db()->query('
				INSERT INTO session (session, login_id, date, `create`, active, ip) 
				VALUES("'.$this->id().'","'.$this->user()->id.'",NOW(),NOW(),1,"'.$this->ip().'");
			');
			return true;
			
		} else {
			$this->destroy();
			return false;
		}
	}



}
