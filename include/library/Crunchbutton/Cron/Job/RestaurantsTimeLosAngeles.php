<?php

class Crunchbutton_Cron_Job_RestaurantsTimeLosAngeles extends Crunchbutton_Cron_Log {

	public function run(){

		Crunchbutton_Restaurant_Time::store( 'America/Los_Angeles' );

		$this->finished();
	}
}