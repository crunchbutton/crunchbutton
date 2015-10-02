<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {
	public function init() {
		echo '1';
		Crunchbutton_Community_Shift::warningDriversBeforeTheirShift();
		echo '2';
		Crunchbutton_Admin_Shift_Assign_Confirmation::warningDriversBeforeTheirShift();
		echo '3';
	}
}