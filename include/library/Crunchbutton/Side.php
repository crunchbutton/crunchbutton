<?php

class Crunchbutton_Side extends Cana_Table {
	public function exports() {
		$out = $this->properties();
		$out['price'] = number_format($out['price'],2);
		return $out;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('side')
			->idVar('id_side')
			->load($id);
	}
}