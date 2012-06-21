<?php

class Crunchbutton_Note extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('note')
			->idVar('id_note')
			->load($id);
	}
}