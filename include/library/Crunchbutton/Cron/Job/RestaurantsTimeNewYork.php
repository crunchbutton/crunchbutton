<?php

class Crunchbutton_Cron_Job_RestaurantsTimeNewYork extends Crunchbutton_Cron_Log {

	public function run(){

		Crunchbutton_Restaurant_Time::store( 'America/New_York' );

		$this->finished();
	}
}