<?php

class Crunchbutton_Cron_Job_TicketForCampusCashOrders extends Crunchbutton_Cron_Log {

	public function run(){

		Order::ticketForCampusCashOrder();

		// it always must call finished method at the end
		$this->finished();
	}
}