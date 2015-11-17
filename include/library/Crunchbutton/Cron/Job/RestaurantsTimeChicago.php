<?php

class Crunchbutton_Cron_Job_RestaurantsTimeChicago extends Crunchbutton_Cron_Log {

	public function run(){

		Crunchbutton_Restaurant_Time::store( 'America/Chicago' );

		$this->finished();
	}
}