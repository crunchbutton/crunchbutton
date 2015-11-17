<?php

class Crunchbutton_Cron_Job_RestaurantsTime extends Crunchbutton_Cron_Log {

	public function run(){

		Crunchbutton_Restaurant_Time::store();

		$this->finished();
	}
}