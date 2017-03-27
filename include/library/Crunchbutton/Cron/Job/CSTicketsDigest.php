<?php

class Crunchbutton_Cron_Job_CSTicketsDigest extends Crunchbutton_Cron_Log {
	public function run($params){
		$tickets = Crunchbutton_Support::dailyDigest( 1 );
		$params = array( 'to' => 'digests@crunchbutton.com', 'tickets' => $tickets );
		$email = new Crunchbutton_Email_CSDigest( $params );
		$email->send();
		// echo $email->message();
		// it always must call finished method at the end
		$this->finished();
	}
}