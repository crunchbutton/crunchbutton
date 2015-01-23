<?php

class Controller_api_test extends Cana_Controller {

	public function init(){

		$cron = new Crunchbutton_Cron_Job_RestaurantFixNotify;
		$cron->run();
		// Crunchbutton_Pexcard_Transaction::getOrderExpenses( '01/18/2015', '01/19/2015' );
		// Crunchbutton_Community_Shift::pexCardRemoveShiftFunds();
		// Crunchbutton_Community_Shift::pexCardRemoveShiftFunds();

	}

}