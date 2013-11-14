<?php

class Crunchbutton_Agent extends Cana_Table {

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('agent')
			->idVar('id_agent')
			->load($id);
	}

	public function getAgent(){
		$userAgent = new Cana_UserAgent();
		$query = "SELECT * FROM agent WHERE browser='{$userAgent->getBrowserName()}' AND version = '{$userAgent->getBrowserVersion()}' AND os = '{$userAgent->getOperatingSystem()}' AND engine = '{$userAgent->getEngine()}' LIMIT 1";
		$agent = Crunchbutton_Agent::q( $query );
		if( !$agent->id_agent ){
			$agent = new Crunchbutton_Agent;
			$agent->browser = $userAgent->getBrowserName();
			$agent->version = $userAgent->getBrowserVersion();
			$agent->engine = $userAgent->getEngine();
			$agent->os = $userAgent->getOperatingSystem();
			$agent->save();
		}
		return $agent;
	}
}