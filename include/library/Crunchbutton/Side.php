<?php

class Crunchbutton_Side extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('side')
			->idVar('id_side')
			->load($id);
	}
}