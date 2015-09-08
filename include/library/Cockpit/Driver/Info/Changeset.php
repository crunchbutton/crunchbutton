<?php

class Cockpit_Driver_Info_Changeset extends Cana_Table {

	public function driver_info() {
		if (!isset($this->_driver_info)) {
			$this->_driver_info = Cockpit_Driver_Info::o($this->id_driver_info);
		}
		return $this->_driver_info;
	}

	public function changes() {
		if (!isset($this->_changes)) {
			$this->_changes = Cockpit_Driver_Info_Change::q('
				SELECT * FROM driver_info_change
				WHERE
					id_driver_info_change_set="'.$this->id_driver_info_change_set.'"
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
			->table('driver_info_change_set')
			->idVar('id_driver_info_change_set')
			->load($id);
	}
}