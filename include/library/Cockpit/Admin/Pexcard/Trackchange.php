<?php

class Cockpit_Admin_Pexcard_Trackchange extends Cana_Table {
	public function save($new = false) {
		if ($this->{$this->idVar()}) {
			$this->_changeSet = new Cockpit_Admin_Pexcard_Trackchange(Cana_Changeset::save($this));
		}
		parent::save();
	}

	public function changeSet() {
		if (!isset($this->_changeSet)) {
			$sets = $this->changeSets();
			$this->_changeSet = array_pop($sets);
		}
		return $this->_changeSet;
	}
}