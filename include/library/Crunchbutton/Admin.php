<?php

class Crunchbutton_Admin extends Cana_Table {
	public static function login($login) {
		return Crunchbutton_Admin::q('select * from admin where login="'.c::db()->escape($login).'" limit 1')->get(0);
	}
	
	public function timezone() {
		if (!isset($this->_timezone)) {
			$this->_timezone = new DateTimeZone($this->timezone);
		}
		return $this->_timezone;
	}
	
	public function permission() {
		if (!isset($this->_permission)) {
			$this->_permission = new Crunchbutton_Acl_Admin($this);
		}
		return $this->_permission;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('admin')
			->idVar('id_admin')
			->load($id);
	}
}