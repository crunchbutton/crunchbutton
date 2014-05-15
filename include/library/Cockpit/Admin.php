<?php

class Cockpit_Admin extends Crunchbutton_Admin {
	public function location() {
		if (!isset($this->_location)) {
			$this->_location = Admin_Location::q('SELECT * FROM admin_location WHERE id_admin="'.$this->id_admin.'" ORDER BY date DESC LIMIT 1')->get(0);
		}
		return $this->_location;
	}
}