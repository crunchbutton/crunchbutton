<?php

class Crunchbutton_Community_Change extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('community_change')
			->idVar('id_community_change')
			->load($id);
	}
}