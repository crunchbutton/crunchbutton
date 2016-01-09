<?php

class Crunchbutton_Community_Shift_Change extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('community_shift_change')
			->idVar('id_community_shift_change')
			->load($id);
	}
}