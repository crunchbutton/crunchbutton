<?php

class Controller_Api_Test_Sandbox extends Cana_Controller {
	public function init(){
		// nothing here
		$tickets = Crunchbutton_Support::dailyDigest( 1 );
		$params = array( 'to' => 'digests@_DOMAIN_', 'tickets' => $tickets );
		$email = new Crunchbutton_Email_CSDigest( $params );
		// $email->send();
		echo $email->message();
	}
}