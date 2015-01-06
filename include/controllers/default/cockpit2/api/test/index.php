<?php

class Controller_api_test extends Crunchbutton_Controller_Rest {
	public function init() {
		$admin_pexcard = Cockpit_Admin_Pexcard::getByPexcard( 100296 )->get( 0 );
		$admin_pexcard->addArbitraryFunds( 1, 'test' );
	}
}