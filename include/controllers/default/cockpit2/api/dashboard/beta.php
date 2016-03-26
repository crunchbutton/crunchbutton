<?php

class Controller_api_dashboard_beta extends Crunchbutton_Controller_RestAccount {
	public function init() {
		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud'])) {
			$this->error(401, true);
		}
		switch (c::getPagePiece(3)) {
			case 'communities-with-shift':
				$out = [];
				$communities = Dashboard::communitiesWithShits();
				foreach($communities as $community){
					$dashboard = new Cockpit_Dashboard($community);
					$out[] = $dashboard->statusByCommunity($community);
				}
				echo json_encode( $out );exit;
				break;
			case 'communities':
				$out = [];
				$communities = $this->request()[ 'communities' ];
				foreach($communities as $community){
					$dashboard = new Cockpit_Dashboard($community);
					$out[] = $dashboard->statusByCommunity($community);
				}
				echo json_encode( $out );exit;
				break;
			default:
				# code...
				break;
		}
	}

}
