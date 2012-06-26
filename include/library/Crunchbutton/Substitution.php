<?php

class Crunchbutton_Substitution extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('substitution')
			->idVar('id_substitution')
			->load($id);
	}
}