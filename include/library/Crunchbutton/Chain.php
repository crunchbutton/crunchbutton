<?php

class Crunchbutton_Chain extends Cana_Table{
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('chain')
			->idVar('id_chain')
			->load($id);
	}
}
