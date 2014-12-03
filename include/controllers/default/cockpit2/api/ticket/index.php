<?php

class Controller_api_ticket extends Crunchbutton_Controller_RestAccount {

	public function init() {
		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud'])) {
			header('HTTP/1.1 401 Unauthorized');
			exit;
		}
		
		$ticket = Support::o(c::getPagePiece(2));

		if (!$ticket->id_support) {
			header('HTTP/1.0 404 Not Found');
			exit;
		}
		
		if (get_class($ticket) != 'Crunchbutton_Support') {
			$ticket = $ticket->get(0);
		}

		if ($this->method() == 'get') {
			echo $ticket->json();
			exit;
		}

		if (c::getPagePiece(3) == 'message' && $this->method() == 'post') {
			$message = $ticket->addAdminMessage([
				'body' => $this->request()['body'],
				'guid' => $this->request()['guid'],
				'phone' => c::admin()->phone,
				'id_admin' => c::admin()->id_admin
			]);
			if ($ticket->id_support_message) {
				$ticket->notify();
			}
			echo $message->json();
			exit;
		}

		header('HTTP/1.0 409 Conflict');
		exit;

	}
}