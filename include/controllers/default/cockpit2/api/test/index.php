<?php

class Controller_api_test extends Crunchbutton_Controller_RestAccount {
	public function init() {
		// Create the fucking password!

		echo Crunchbutton_Util::randomPass();


	}
}