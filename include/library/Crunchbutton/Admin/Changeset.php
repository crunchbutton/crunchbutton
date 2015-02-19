<?php

class Crunchbutton_Admin_Changeset extends Cana_Table {

	public function admin() {
		if (!isset($this->_admin)) {
			$this->_admin = Admin::o($this->id_admin);
		}
		return $this->_admin;
	}

	public function changes() {
		if (!isset($this->_changes)) {
			$this->_changes = Crunchbutton_Admin_Change::q('
				SELECT * FROM admin_change
				WHERE
					id_admin_change_set="'.$this->id_admin_change_set.'"
			');
		}
		return $this->_changes;
	}
	
	public function author() {
		if (!isset($this->_author)) {
			$this->_author = Admin::o($this->id_admin);
		}
		return $this->_author;
	}

	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime($this->timestamp, new DateTimeZone(c::config()->timezone));
		}
		return $this->_date;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('admin_change_set')
			->idVar('id_admin_change_set')
			->load($id);
	}
}