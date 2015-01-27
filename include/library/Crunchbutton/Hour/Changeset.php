<?php
//die('wasssuppp');
class Crunchbutton_Hour_Changeset extends Cana_Table {

	public function hour() {
		if (!isset($this->_hour)) {
			$this->_hour = Hour::o($this->id_hour);
		}
		return $this->_hour;
	}

	public function changes() {
		if (!isset($this->_changes)) {
			$this->_changes = Crunchbutton_Hour_Change::q('
				SELECT * FROM hour_change
				WHERE
					id_hour_change_set="'.$this->id_hour_change_set.'"
			');
		}
		return $this->_changes;
	}
	
	public function admin() {
		if (!isset($this->_admin)) {
			$this->_admin = Admin::o($this->id_admin);
		}
		return $this->_admin;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('hour_change_set')
			->idVar('id_hour_change_set')
			->load($id);
	}
}