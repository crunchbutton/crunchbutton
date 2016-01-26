<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init(){
		$pex = Cockpit_Admin_Pexcard::o( 2905 );
		echo '<pre>';var_dump( $pex->createQueRemoveFunds() );exit();
	}
}
