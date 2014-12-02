<?php

class Controller_api_call extends Crunchbutton_Controller_RestAccount {

	public function init() {
		
		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud'])) {
			header('HTTP/1.1 401 Unauthorized');
			exit;
		}

		$call = Call::o(c::getPagePiece(2));

		if (!$call->id_call) {
			header('HTTP/1.0 404 Not Found');
			exit;
		}

		switch ($this->method()) {
			case 'get':
				echo $call->json();
				break;

			case 'post':
				// do nothing for now
				break;
		}
	}
}