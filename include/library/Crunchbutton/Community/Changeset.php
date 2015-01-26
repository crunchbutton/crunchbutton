<?php

class Crunchbutton_Community_Changeset extends Cana_Table {

	public function community() {
		if (!isset($this->_community)) {
			$this->_community = Community::o($this->id_community);
		}
		return $this->_community;
	}

	public function changes() {
		if (!isset($this->_changes)) {
			$this->_changes = Crunchbutton_Community_Change::q('
				SELECT * FROM community_change
				WHERE
					id_community_change_set="'.$this->id_community_change_set.'"
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
			->table('community_change_set')
			->idVar('id_community_change_set')
			->load($id);
	}
}