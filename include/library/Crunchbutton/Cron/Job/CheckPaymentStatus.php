<?php

class Crunchbutton_Cron_Job_CheckPaymentStatus extends Crunchbutton_Cron_Log {

	public function run(){

		$settlement = new Crunchbutton_Settlement;

		$settlement->checkPaymentStatus();

		$settlement->checkSucceededPaymentStatus();

		// it always must call finished method at the end
		$this->finished();
	}
}