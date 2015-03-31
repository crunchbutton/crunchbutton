<?php


class Crunchbutton_Cron_Job_CSTicketsDigest extends Crunchbutton_Cron_Log {

	public function run($params){

		$supports = Crunchbutton_Support::q('SELECT
			support.*
			FROM support
			WHERE
			support.type != "WARNING"
			AND support.datetime > date_sub(now(), interval 1 day)
			ORDER BY support.id_support ASC
			limit 250' );

		$params = array( 'to' => 'digests@_DOMAIN_', 'messages'=> $supports );
		$email = new Crunchbutton_Email_CSDigest($params);
		$email->send();

		// it always must call finished method at the end
		$this->finished();
	}
}