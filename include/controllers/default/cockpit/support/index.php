<?php

class Controller_support extends Crunchbutton_Controller_Account {
	public function init() {
		$support = Support::o(c::getPagePiece(2));
		c::view()->page = 'support';

		if( $support->id_support ){
			// Show the support's form
			c::view()->support = $support;
			c::view()->display('support/support');	
		} else {
			// Show the support's list
			c::view()->display('support/index');
		}
	}
}