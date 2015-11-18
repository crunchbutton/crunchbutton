<?php

class Crunchbutton_Session_Token extends Cana_Table {

	public function generateAndSaveToken($user, $admin, $session) {
		if (($this->id_user || $this->id_admin) && !$this->token) {
			$fields = '-=d4sh0fs4|t?&4ndM4YB350m35ymb0||0v3!!!!!!=-'.$this->id_session.$this->id_user.$this->id_admin.uniqid();
			$this->token = strtoupper(hash('sha512', $fields));
			$this->save();
		}
	}

	public static function token($token) {
		if (!$token) return false;
		$res = Cana::db()->query('select * from session where token=?', [$token]);
		$session = $res->fetch();
		//$session->closeCursor();

		if ($session->id_session) {
			return $session;
		}
		return false;
	}

	public static function deleteToken($token) {
		if (!$token) return false;
		Cana::db()->query('delete from session where token=?',[$token]);
	}

	public function auth() {
		if (!isset($this->_auth)) {
			$this->_auth = new Crunchbutton_User_Auth($this->id_user_auth);
		}
		return $this->_auth;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('session')
			->idVar('id_session')
			->load($id);

	}
}
