<?php

class Controller_api_customer extends Crunchbutton_Controller_RestAccount {

	public function init() {
		
		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud'])) {
			header('HTTP/1.1 401 Unauthorized');
			exit;
		}
		
		$customer = User::uuid(c::getPagePiece(2));

		if (!$customer->id_user) {
			$customer = User::o(c::getPagePiece(2));
		}
		
		if (!$customer->id_user) {
			$customer = User::byPhone(c::getPagePiece(2), false);
		}		
/*
		if (get_class($customer) != 'Crunchbutton_User') {
			$customer = $customer->get(0);
		}
*/

		if (!$customer->id_user) {
			header('HTTP/1.0 404 Not Found');
			exit;
		}

		switch ($this->method()) {
			case 'get':
				echo $customer->json();
				break;

			case 'post':
				// do nothing for now
				break;
		}
	}
}