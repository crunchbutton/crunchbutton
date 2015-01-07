<?php

class Crunchbutton_Cron_Job_PexCardTransferErrorCheck extends Crunchbutton_Cron_Log {

	public function run(){

		Crunchbutton_Pexcard_Action::monitor();

		// it always must call finished method at the end
		$this->finished();
	}
}