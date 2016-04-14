<?php

class Crunchbutton_Cron_Job_MarketingMaterialsRefil extends Crunchbutton_Cron_Log {
	public function run(){
		Cockpit_Marketing_Materials_Refil::sendToGitHub();
		$this->finished();
	}
}
