<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {
	public function init() {

		Crunchbutton_Community_Shift::pexCardRemoveShiftFunds();

	}
}