<?php

class Crunchbutton_Cron_Job_PayRestaurants extends Crunchbutton_Cron_Log {

	public function run(){

		$settlement = new Crunchbutton_Settlement;

		$settlement->doRestaurantsPayments();

		// it always must call finished method at the end
		$this->finished();
	}
}
