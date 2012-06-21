<?php

class Controller_api_v1_project extends Crunchbutton_Controller_Rest {
	public function init() {
		$project = Project::o(c::getPagePiece(3));
		
		switch (c::getPagePiece(4)) {
			case 'deliveries':
				echo $project->deliveries()->json();
				break;
			default:
				echo $project->json();
				break;
		}
	}
}