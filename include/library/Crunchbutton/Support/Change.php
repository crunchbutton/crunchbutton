<?php

class Crunchbutton_Support_Change extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('support_change')
			->idVar('id_support_change')
			->load($id);
	}
}