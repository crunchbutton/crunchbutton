<?php

class Crunchbutton_Cron_Job_CommunityClosedMessage extends Crunchbutton_Cron_Log {

	public function run(){

		$communities = Crunchbutton_Community::q( 'SELECT * FROM community' );
		foreach( $communities as $community ){
			$community->saveClosedMessage();
		}

		// it always must call finished method at the end
		$this->finished();
	}
}