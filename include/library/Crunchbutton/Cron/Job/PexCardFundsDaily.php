<?php

class Crunchbutton_Cron_Job_PexCardFundsDaily extends Crunchbutton_Cron_Log {

	public function run(){

		Cockpit_Admin_Pexcard::pexCardRemoveCardFundsDaily();

		// it always must call finished method at the end
		$this->finished();
	}
}