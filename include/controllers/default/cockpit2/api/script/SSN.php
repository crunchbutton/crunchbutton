<?php

// Prep for Settlement Test #3516
class Controller_Api_Script_SSN extends Crunchbutton_Controller_RestAccount {

	public function init() {

		die('No!');

		$ssns = Crunchbutton_Admin_Payment_Type::q( 'SELECT * FROM admin_payment_type WHERE social_security_number IS NOT NULL' );
		foreach ( $ssns as $ssn ) {
			// $admin = Crunchbutton_Admin::o( $ssn->id_admin );
			// $admin->ssn( $ssn->social_security_number );
			// echo $ssn->social_security_number . ' :: ' . $admin->ssn();
			// echo "\n";
		}

	}
}