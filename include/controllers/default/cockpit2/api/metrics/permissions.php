<?php

/**
 * Mini-handler to allow front end to list just the communities to which the
 * user has permission to access metrics..
 **/
class Controller_api_Metrics_Permissions extends Crunchbutton_Controller_RestAccount {
	public function init() {
		$out = [];
		$communities = Cockpit_Metrics::availableCommunities();
		if (c::admin()->permission()->check(['global' ])) {
			$out[] = [ 'id_community' => -1, 'name' => 'All Communities', 'active' => true ];
		}
		foreach($communities as $c) {
			$out[] = [ 'id_community' => $c->id_community, 'name' => $c->name, 'active' => $c->active ];
		}
		echo json_encode($out);
	}
}

?>
