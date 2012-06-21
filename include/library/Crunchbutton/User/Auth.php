<?php

class Crunchbutton_User_Auth extends Cana_Table {
	public static function byTypeId($type, $id) {
		 $row = Cana::db()->get('
			SELECT * 
			FROM user_auth
			WHERE
				type="'.$type.'"
				AND auth="'.$id.'"
			LIMIT 1
		');
		return new Crunchbutton_User_Auth($row);
	}
	
	public static function byUser($user) {
		 $res = Cana::db()->query('
			SELECT * 
			FROM user_auth
			WHERE
				id_user="'.$user.'"
				AND active=1
		');
		$auths = [];
		while ($row = $res->fetch()) {
			$auths[$row->id_user_auth] = new Crunchbutton_User_Auth($row);
		}
		return $auths;
	}
	
	public function user() {
		if (!isset($this->_user)) {
			return new Crunchbutton_User($this->id_user);
		}
		return $this->_user;
	}
	
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('user_auth')
			->idVar('id_user_auth')
			->load($id); 
	}
}