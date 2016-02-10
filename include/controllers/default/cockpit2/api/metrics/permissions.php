<?php

/**
 * Mini-handler to allow front end to list just the communities to which the
 * user has permission to access metrics..
 **/
class Controller_api_Metrics_Permissions extends Crunchbutton_Controller_RestAccount {
	public function init() {
		$out = [];

		$top = [];
		$query = "SELECT COUNT(*) AS orders , id_community FROM `order` o WHERE o.date BETWEEN NOW() - INTERVAL 30 DAY AND NOW() GROUP BY id_community ORDER BY orders DESC LIMIT 5";
		$communities = c::db()->get( $query );
		foreach( $communities as $community ){
			$top[ $community->id_community ] = true;
		}

		$communities = Cockpit_Metrics::availableCommunities();
		if (c::admin()->permission()->check(['global' ])) {
			$out[] = [ 'id_community' => -1, 'name' => 'All Communities', 'active' => true ];
		}
		foreach($communities as $c) {
			$out[] = [ 'id_community' => $c->id_community, 'name' => $c->name, 'active' => $c->active, 'top' => $top[ $c->id_community ] ];
		}
		echo json_encode($out);
	}
}

?>
