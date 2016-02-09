<?php

class Crunchbutton_Cron_Job_CreatePexCardToken extends Crunchbutton_Cron_Log {

	public function run(){

		Crunchbutton_Pexcard_Token::createToken();

		// it always must call finished method at the end
		$this->finished();
	}
}
