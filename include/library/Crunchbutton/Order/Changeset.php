<?php

class Crunchbutton_Order_Changeset extends Cana_Table {

	public function order() {
		if (!isset($this->_order)) {
			$this->_order = Order::o($this->id_order);
		}
		return $this->_order;
	}

	public function changes() {
		if (!isset($this->_changes)) {
			$this->_changes = Crunchbutton_Order_Change::q('
				SELECT * FROM order_change
				WHERE
					id_order_change_set="'.$this->id_order_change_set.'"
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
	
	public function user() {
		if (!isset($this->_user)) {
			$this->_user = User::o($this->id_user);
		}
		return $this->_user;
	}
	
	public function author() {
		return $this->admin()->id_admin ? $this->admin() : $this->user();
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('order_change_set')
			->idVar('id_order_change_set')
			->load($id);
	}
}