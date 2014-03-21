<?php

class Controller_tests_shifts extends Crunchbutton_Controller_Account {

	public function init() {
		echo '<pre>';
		Crunchbutton_Community_Shift::sendWarningToDrivers();
	}
}
