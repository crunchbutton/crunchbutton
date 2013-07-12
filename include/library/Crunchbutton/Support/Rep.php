<?php

class Crunchbutton_Support_Rep extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('support_rep')
			->idVar('id_support_rep')
			->load($id);
	}

	public static function getLoggedInRep() {
		$rep = Support_Rep::q('
			select * from support_rep where name="'.c::admin()->login.'" limit 1
		');
		return $rep;
	}
}
