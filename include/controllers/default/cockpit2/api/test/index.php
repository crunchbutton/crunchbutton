<?php

class Controller_api_test extends Crunchbutton_Controller_RestAccount {
	public function init() {
		$email = new Cockpit_Email_Driver_Setup( [ 'id_admin' => 5 ] );
		echo '<pre>';var_dump( $email->send() );exit();;
	}
}