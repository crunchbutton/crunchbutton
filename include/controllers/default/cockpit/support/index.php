<?php

class Controller_support extends Crunchbutton_Controller_Account {
	public function init() {
		c::view()->layout('layout/feather');
		$support = Support::o(c::getPagePiece(1));
		c::view()->page = 'support';

		if( $support->id_support ){
			// Show the support's form
			c::view()->support = $support;
			c::view()->title = '#SUP' . $support->id_support;
			c::view()->display('m/support/support');	
		} else {
			// Show the support's list
			c::view()->supports = Support::q('select * from support order by id_support desc limit 5');
			c::view()->title = 'Support';
			c::view()->display('m/support/index');
		}
	}
}
