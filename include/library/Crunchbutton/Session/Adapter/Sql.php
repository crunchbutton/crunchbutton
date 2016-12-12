<?php

class Crunchbutton_Session_Adapter_Sql extends Cana_Table implements SessionHandlerInterface {
	public function destroy($id = null) {
		// $id is the new session aparantly
		// $this->id_session doesnt seem to work
		Cana::dbWrite()->query('delete from session where id_session=?',[$id]);

		if (c::auth()->session()->adapter()->id_session) {
			//Cana::dbWrite()->query('delete from session where id_session=?',[c::auth()->session()->adapter()->id_session]);
		}
		return true;
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
	public function get($var = null) {
		return $_SESSION[$var];
	}

	public function open($savePath, $sessionName) {
		$this
			->table('session')
			->idVar('id_session')
			->load(session_id());

		if (!$this->id_session) {
			$this->write();
		}

		return true;
	}

	public function close() {
		return true;
	}

	public function read($id = null) {
		return $this->data;
	}

	public function write($id = null, $data = null, $token = null) {
		if (!$this->id_session) {
			$this->date_create = date('Y-m-d H:i:s');
			$this->active = 1;
			// $this->id_user = $this->id_user || $this->get('id_user');
			// $this->id_admin = $this->id_admin || $this->get('id_admin');
			$this->ip = c::getIp();
			$this->id_session = $id;
		}

		if (!is_null($token)) {
			$this->token = $token;
		}

		$this->date_activity = date('Y-m-d H:i:s');
		$this->data = $data;

		if(!$this->id_session && !$id){
			$id = session_id();
		}

		if ($this->id_session) {
			$this->save();
		} elseif ($id) {
			$this->save($id);
		}

		$sess = new Session_Adapter_Sql($id);
		if ($_ENV['DEBUG']) {
			error_log('>> SAVING USER...'.$this->id_user);
		}

		try {
			if ($sess->id_session) {
				$this->save();
			} elseif ($id) {
				$this->save($id);
			} else {
				// no session id?
			}

		} catch (Exception $e) {}

		return true;
	}

	public function gc($maxlifetime) {
		// only delete if there is no token
		//Cana::dbWrite()->query('DELETE FROM session WHERE date_activity < "'.(time() - $maxlifetime).'" and token is null');
		return true;
	}

	public function generateAndSaveToken() {
		if (($this->id_user || $this->id_admin) && !$this->token) {
			if ($_ENV['DEBUG']) {
				error_log('saving SQL user '. $this->id_user);
				error_log('saving SQL token '. $this->token);
			}
			$fields = '-=d4sh0fs4|t?&4ndM4YB350m35ymb0||0v3!!!!!!=-'.$this->id_session.$this->id_user.$this->id_admin.uniqid();
			$this->token = strtoupper(hash('sha512', $fields));

			if ($this->id_session) {
				$this->save();
			}
		}
		return true;
	}

	public static function token($token) {
		return false;
	}

	public static function deleteToken($token) {
		if (!$token) return false;
		Cana::dbWrite()->query('delete from session where token=?',[$token]);
	}

	public function auth() {
		if (!isset($this->_auth)) {
			$this->_auth = new Crunchbutton_User_Auth($this->id_user_auth);
		}
		return $this->_auth;
	}

	public function __construct($id = null) {
		parent::__construct();
		if (get_class($this) == 'Crunchbutton_Session_Adapter_Sql') {
			$this
				->table('session')
				->idVar('id_session')
				->load($id);
		}
	}

	public function user() {
		return true;
	}
}
