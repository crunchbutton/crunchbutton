<?php

class Crunchbutton_Cron_Job_RestaurantsTimeDenver extends Crunchbutton_Cron_Log {

	public function run(){

		Crunchbutton_Restaurant_Time::store( 'America/Denver' );

		$this->finished();
	}
}