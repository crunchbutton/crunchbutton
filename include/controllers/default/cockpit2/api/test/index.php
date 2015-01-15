<?php

class Controller_api_test extends Cana_Controller {

	public function init(){

		// Cockpit_Bounce_Back::run();
		Crunchbutton_Pexcard_Transaction::loadTransactions();

	}

}