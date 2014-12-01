<?php

class Crunchbutton_Cron_Job_PexCardFunds extends Crunchbutton_Cron_Log {

	public function run(){

		Crunchbutton_Community_Shift::pexCardAddOrRemoveShiftFunds();

		// it always must call finished method at the end
		$this->finished();
	}
}