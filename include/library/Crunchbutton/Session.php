<?php

class Crunchbutton_Session extends Cana_Table {

	public function __construct($id = null) {
		session_set_save_handler(
			array($this, 'open'),
			array($this, 'close'),
			array($this, 'read'),
			array($this, 'write'),
			array($this, 'destroy'),
			array($this, 'gc')
		);
		parent::__construct();
	}
	
	public function destroy($id = null) {
		if ($this->id_session) {
			$this->delete();
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
		}

		$this->date_activity = date('Y-m-d H:i:s');
		$this->data = $data;
		$this->save($this->id_session ? null : $id);
	}

	public function gc($maxlifetime) {
		Cana::db()->query('DELETE FROM session WHERE date_activity < "'.(time() - $maxlifetime).'"');
	}
	
	public function auth() {
		if (!isset($this->_auth)) {
			$this->_auth = new Crunchbutton_User_Auth($this->id_user_auth);
		}
		return $this->_auth;
	}
}