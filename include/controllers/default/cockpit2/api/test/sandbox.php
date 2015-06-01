<?php

class Controller_Api_Test_Sandbox extends Crunchbutton_Controller_Account {
	public function init() {

		$admin = Admin::o( 5 );
		echo '<pre>';var_dump( $admin->communitiesHeDeliveriesFor() );exit();


	}
}