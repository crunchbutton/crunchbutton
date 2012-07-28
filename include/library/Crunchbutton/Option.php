<?php

class Crunchbutton_Option extends Cana_Table {
	public function exports() {
		$out = $this->properties();
		$out['price'] = number_format($out['price'],2);
		return $out;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('option')
			->idVar('id_option')
			->load($id);
	}
}