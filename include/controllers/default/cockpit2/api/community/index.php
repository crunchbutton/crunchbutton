<?php

class Controller_api_community extends Crunchbutton_Controller_RestAccount {

	public function init() {
		
		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud'])) {
			header('HTTP/1.1 401 Unauthorized');
			exit;
		}
		
		$community = Community::permalink(c::getPagePiece(2));

		if (!$community->id_community) {
			$community = Community::o(c::getPagePiece(2));
		}

		if (!$community->id_community) {
			header('HTTP/1.0 404 Not Found');
			exit;
		}

		switch ($this->method()) {
			case 'get':
				echo $community->json();
				break;

			case 'post':
				// do nothing for now
				break;
		}
	}
}