<?php

class Cana_Table_Trackchange extends Cana_Table {
	public function save() {
		if ($this->{$this->idVar()}) {
			$objectName = get_class($this);
			$objectName .= '_Changeset';
			$this->_changeSet = new $objectName(Cana_Changeset::save($this));
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