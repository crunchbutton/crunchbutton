<?php

class Crunchbutton_Community_Shift_Changeset extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('community_shift_change_set')
			->idVar('id_community_shift_change_set')
			->load($id);
	}
}