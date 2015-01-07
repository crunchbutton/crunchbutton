<?php

class Controller_api_test extends Crunchbutton_Controller_Rest {
	public function init() {
		Crunchbutton_Community_Shift::removeRecurring( 6001 );

	}
}