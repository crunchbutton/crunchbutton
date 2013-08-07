<?php

class Crunchbutton_Session_Adapter extends Cana_Table {
	public function destroy($id = null) {
		if ($this->id_session) {
			$this->delete();
		}
	}
	
	public function save($newItem = 0) {
		if ($this->id_session || $newItem) {
			parent::save($newItem);
		}
	}
	
	public function set($var, $value) {
		$_SESSION[$var] = $value;
		return $this;
	}
	public function get($var) {
		return $_SESSION[$var];
	}
	
	public function open($savePath, $sessionName) {
		$this
			->table('session')
			->idVar('id_session')
			->load(session_id());

		return true;
	}

	public function close() {
		return true;
	}

	public function read($id = null) {
		return $this->data;
	}

	public function write($id = null, $data = null) {
		if (!$this->id_session) {
			$this->date_create = date('Y-m-d H:i:s');
			$this->active = 1;
			$this->id_user = $this->get('id_user');
			$this->ip = $_SERVER['REMOTE_ADDR'];
			$this->id_session = $id;
		}

		$this->date_activity = date('Y-m-d H:i:s');
		$this->data = $data;

		$sess = new Session_Adapter($id);
		try {
			if ($sess->id_session) {
				$this->save();
			} elseif ($id) {
				$this->save($id);
			} else {
				// no session id?
			}

		} catch (Exception $e) {}
	}

	public function gc($maxlifetime) {
		// only delete if there is no token
		Cana::db()->query('DELETE FROM session WHERE date_activity < "'.(time() - $maxlifetime).'" and token is null');
	}
	
	public function generateAndSaveToken() {
		if ($this->id_user && !$this->token) {
			$fields = '-=d4sh0fs4|t?&4ndM4YB350m35ymb0||0v3!!!!!!=-'.$this->id_session.$this->id_user.uniqid();
			$this->token = strtoupper(hash('sha512', $fields));
			$this->save();
		}
	}
	
	public static function token($token) {
		if (!$token) return false;
		// Remove the '"' of $totalStorage
		$token = str_replace( '"', '', $token );
		$res = Cana::db()->query('select * from session where token="'.c::db()->escape($token).'"');
		$session = $res->fetch();

		if ($session->id_session) {
			return $session;
		}
		return false;
	}
	
	public static function deleteToken($token) {
		if (!$token) return false;
		Cana::db()->query('delete from session where token="'.c::db()->escape($token).'"');	
	}
	
	public function auth() {
		if (!isset($this->_auth)) {
			$this->_auth = new Crunchbutton_User_Auth($this->id_user_auth);
		}
		return $this->_auth;
	}
	
	public function __construct($id = null) {
		parent::__construct();
		if (get_class($this) == 'Crunchbutton_Session_Adapter') {
			$this
				->table('session')
				->idVar('id_session')
				->load($id);
		}
	}
}