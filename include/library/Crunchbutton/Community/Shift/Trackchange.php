<?php

class Crunchbutton_Community_Shift_Trackchange extends Cana_Table {
	public function save($new = false) {
		if ($this->{$this->idVar()}) {
			$this->_changeSet = new Crunchbutton_Community_Shift_Changeset(Cana_Changeset::save($this));
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