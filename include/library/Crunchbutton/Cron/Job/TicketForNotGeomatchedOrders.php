<?php

class Crunchbutton_Cron_Job_TicketForNotGeomatchedOrders extends Crunchbutton_Cron_Log {

	public function run(){

		Order::ticketsForNotGeomatchedOrders();

		// it always must call finished method at the end
		$this->finished();
	}
}