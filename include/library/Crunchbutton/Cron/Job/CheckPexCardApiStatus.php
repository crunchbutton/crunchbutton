<?php

class Crunchbutton_Cron_Job_CheckPexCardApiStatus extends Crunchbutton_Cron_Log {

	public function run(){
		try {
			$result = Crunchbutton_Pexcard_Resource::ping();
			$result = trim( $result );
			if( strpos( $result, date( 'Y-m' ) ) !== false ){
				// stop the cron
				Crunchbutton_Support::createNewWarning(  [ 'body' => 'Yay! it seems pex card is working again! Please tell that to David or Nick or Devin or Daniel (Issue #5507). Thanks! You are awesome! :)' ] );
				$cron = Crunchbutton_Cron_Log::q( 'SELECT * FROM cron_log WHERE `class` = "Crunchbutton_Cron_Job_CheckPexCardApiStatus"' )->get( 0 );
				$cron->interval_unity = 0;
				$cron->save();
				echo 'ok';
			} else {
				echo 'nope';
			}
		} catch (Exception $e) {
			echo 'nope';
		}
		$this->finished();
	}
}