<?php

class Crunchbutton_Cron_Job_PexLoadTransactions extends Crunchbutton_Cron_Log {

	public function run(){
		Crunchbutton_Pexcard_Transaction::loadTransactions();
		Crunchbutton_Pexcard_Transaction::convertTimeZone();
		// it always must call finished method at the end
		$this->finished();
	}
}