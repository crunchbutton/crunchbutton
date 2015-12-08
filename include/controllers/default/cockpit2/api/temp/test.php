<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init(){
		$c = Crunchbutton_Cron_Job_PexPreProcessReport::o( 111 );
		$c->run();
	}
}
