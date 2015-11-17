<?php

class Crunchbutton_Cron_Job_RestaurantsTimePhoenix extends Crunchbutton_Cron_Log {

	public function run(){

		Crunchbutton_Restaurant_Time::store( 'America/Phoenix' );

		$this->finished();
	}
}