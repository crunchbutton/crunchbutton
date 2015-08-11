<?php

class Controller_api_temp extends Crunchbutton_Controller_RestAccount {

	public function init() {

		Crunchbutton_Admin_Shift_Assign_Confirmation::warningDriversBeforeTheirShift();
	}

}
