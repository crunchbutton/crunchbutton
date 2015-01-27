<?php
//die('wasssuppp');
class Crunchbutton_Support_Changeset extends Cana_Table {

	public function support() {
		if (!isset($this->_support)) {
			$this->_support = Support::o($this->id_support);
		}
		return $this->_support;
	}

	public function changes() {
		if (!isset($this->_changes)) {
			$this->_changes = Crunchbutton_Support_Change::q('
				SELECT * FROM support_change
				WHERE
					id_support_change_set="'.$this->id_support_change_set.'"
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
			->table('support_change_set')
			->idVar('id_support_change_set')
			->load($id);
	}
}