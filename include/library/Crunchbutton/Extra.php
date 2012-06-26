<?php

class Crunchbutton_Extra extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('extra')
			->idVar('id_extra')
			->load($id);
	}
}