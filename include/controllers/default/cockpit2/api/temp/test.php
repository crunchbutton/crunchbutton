<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init(){

		$q = Crunchbutton_Queue_Pexcard_Action::o( 2138424 );
		$q->run();

	}
}
