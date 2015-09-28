<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {
	public function init() {
		$admin = Admin::o( 518 );
		$pexcard = $admin->pexcard();
		$pexcard->removeFundsShiftFinished( 59569 );
	}
}