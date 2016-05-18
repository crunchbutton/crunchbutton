<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init() {
		echo 'starting...';
		$assignment = Crunchbutton_Admin_Shift_Assign::o(82712);
		Crunchbutton_Admin_Shift_Assign_Confirmation::askWithTextMessage($assignment);
		// Crunchbutton_Admin_Shift_Assign_Confirmation::warningDriversBeforeTheirShift();
		echo 'end';
	}
}
