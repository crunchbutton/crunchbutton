<?php

class Controller_Api_Test_Sandbox extends Crunchbutton_Controller_Account {

	public function init() {

		Crunchbutton_Pexcard_Transaction::convertTimeZone();
		// Crunchbutton_Pexcard_Transaction::loadTransactions();

	}
}