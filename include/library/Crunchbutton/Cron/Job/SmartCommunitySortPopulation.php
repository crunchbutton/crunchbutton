<?php

class Crunchbutton_Cron_SmartCommunitySortPopulation extends Crunchbutton_Cron_Log {

	public function run(){

		Crunchbutton_Community::smartSortPopulation();

		// it always must call finished method at the end
		$this->finished();
	}
}
