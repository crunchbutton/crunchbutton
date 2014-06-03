<?php

class Controller_tests_credit extends Crunchbutton_Controller_Account {

	public function init() {
		echo '<pre>';
		$admins = Crunchbutton_Admin::q( 'SELECT * FROM admin' );
		foreach( $admins as $admin ){
			$credits = Crunchbutton_Credit::antifraudByPhone( 1 );
			echo '<pre>';var_dump( $credits );exit();
		}
	}
}
