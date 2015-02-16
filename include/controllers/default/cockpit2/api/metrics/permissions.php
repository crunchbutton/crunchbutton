<?php

/**
 * Mini-handler to allow front end to list just the communities to which the 
 * user has permission to access metrics..
 **/
class Controller_api_Metrics_Permissions extends Crunchbutton_Controller_RestAccount {
	public static function getKeySubset($keys, $obj) {
		$out = [];
		foreach($keys as $k) {
			if(isset($obj[$k])) {
				$out[$k] = $obj[$k];
			}
		}
		return $out;
	}
	public function init() {
		$out = [];
		$keys = ['name', 'id_community'];
		$communities = Cockpit_Metrics::availableCommunities();
		foreach($communities as $c) {
			$out[] = self::getKeySubset($keys, $c->properties());
		}
		echo json_encode($out);
	}
}

?>
