<?php

class Cana_Table_Trackchange extends Cana_Table {
	public function save($new = false) {
		$objectName = $this->_changeSetName ? $this->_changeSetName : get_class($this);
		$objectName .= '_Changeset';

		if (!$this->{$this->idVar()} && $this->_changeOptions['created']) {
			$saveCreated = true;
		}

		// save changes
		if ($this->{$this->idVar()}) {
			$this->_changeSet = new $objectName(Cana_Changeset::save($this, $this->changeOptions() ? $this->changeOptions() : null));
		}

		parent::save();

		// save that it was created
		if ($saveCreated) {
			$this->_changeSet = new $objectName(Cana_Changeset::save($this, $this->changeOptions() ? $this->changeOptions() : null));
		}
	}
	
	public function changeOptions($o = null) {
		if (!is_null($o)) {
			$this->_changeOptions = $o;
		}
		return $this->_changeOptions;
	}

	public function changeSet() {
		if (!isset($this->_changeSet)) {
			$sets = $this->changeSets();
			$this->_changeSet = array_pop($sets);
		}
		return $this->_changeSet;
	}
}