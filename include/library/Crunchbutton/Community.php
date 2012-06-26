<?php

class Crunchbutton_Community extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('community')
			->idVar('id_community')
			->load($id);
	}
}