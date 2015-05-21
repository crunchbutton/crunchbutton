<?php

class Crunchbutton_Cron_Job_Test extends Crunchbutton_Cron_Log {

	public function run(){

		$properties = ( array ) c::db()->dbo();
		foreach( $properties as $p ){
			if( $p->host ){
				$host = $p->host;
			}
		}

		Log::debug( [ 'desc' => 'testing the cron log', 'host' => $host, 'type' => 'cron-jobs' ] );

		// it always must call finished method at the end
		$this->finished();
	}
}