<?php

class Crunchbutton_Group extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('group')
			->idVar('id_group')
			->load($id);
	}
}