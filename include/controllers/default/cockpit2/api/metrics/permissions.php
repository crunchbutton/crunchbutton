<?php

/**
 * Mini-handler to allow front end to list just the communities to which the 
 * user has permission to access metrics..
 **/
class Controller_api_Metrics_Permissions extends Crunchbutton_Controller_RestAccount {
	public function init() {
		$out = [];
		$communities = Cockpit_Metrics::availableCommunities();
		foreach($communities as $c) {
			$out[] = $c->properties();
		}
		echo json_encode($out);
	}
}

?>
