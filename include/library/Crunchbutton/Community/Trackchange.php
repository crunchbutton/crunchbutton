<?php

class Crunchbutton_Community_Trackchange extends Cana_Table {
	public function save($new = false) {
		if ($this->{$this->idVar()}) {

			if( !c::admin()->id_admin ){
				$admin = Admin::login( Crunchbutton_Community::AUTO_SHUTDOWN_COMMUNITY_LOGIN );
				$options = [ 'id_admin' => $admin->id_admin];
			} else {
				$options = [];
			}
			$this->_changeSet = new Crunchbutton_Community_Changeset(Cana_Changeset::save($this));
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