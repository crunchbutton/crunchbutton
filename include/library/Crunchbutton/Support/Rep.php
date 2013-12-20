<?php

class Crunchbutton_Support_Rep extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('support_rep')
			->idVar('id_support_rep')
			->load($id);
	}

	public static function createSupportRep(){
		$admin = c::admin();
		$rep = new Crunchbutton_Support_Rep();
		$rep->name = c::admin()->login;
		$rep->phone = c::admin()->txt;
		$rep->active = 1;
		$rep->save();
		return $rep;
	}

	public static function getLoggedInRep() {
		$rep = Support_Rep::q('select * from support_rep where name="'.c::admin()->login.'" limit 1');
		if( !$rep->id_support_rep ){
			$rep = Crunchbutton_Support_Rep::createSupportRep();
		}
		return $rep;
	}
}
