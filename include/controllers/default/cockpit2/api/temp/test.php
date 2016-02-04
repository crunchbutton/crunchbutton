<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init(){
		header( 'Content-Type: text/html' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		$tickets = Crunchbutton_Support::dailyDigest( 1 );
		$params = array( 'to' => 'digests@_DOMAIN_', 'tickets' => $tickets );
		$email = new Crunchbutton_Email_CSDigest( $params );
		echo $email->message();
	}
}
