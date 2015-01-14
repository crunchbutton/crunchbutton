<?php

class Controller_api_ticket extends Crunchbutton_Controller_RestAccount {

	public function init() {
		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud'])) {
			header('HTTP/1.1 401 Unauthorized');
			exit;
		}

		// Creates a new ticket for a certain order
		if (c::getPagePiece(2) == 'create' && $this->method() == 'post' && $this->request()['id_order']) {
			$order = Order::o($this->request()['id_order']);
			if (!$order->id_order) {
				header('HTTP/1.0 404 Not Found');
				exit;
			}
			$support = $order->getSupport(true);
			echo $support->json();
			exit;
		}

		$ticket = Support::o( c::getPagePiece( 2 ) );

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

		if (c::getPagePiece(3) == 'open-close' && $this->method() == 'post' ) {
			if( $ticket->status == Crunchbutton_Support::STATUS_OPEN ){
				$ticket->status = Crunchbutton_Support::STATUS_CLOSED;
				$ticket->save();
				$ticket->addSystemMessage( c::admin()->name . ' closed this ticket' );
			} else {
				$ticket->status = Crunchbutton_Support::STATUS_OPEN;
				$ticket->save();
				$ticket->addSystemMessage( c::admin()->name . ' opened this ticket' );
			}
			echo $ticket->json();
			exit;
		}
		if (c::getPagePiece(3) == 'message' && $this->method() == 'post') {
			$note = $this->request()[ 'note' ];
			if( $note ){
				$message = $ticket->addNote($this->request()['body']);
			} else {
				$message = $ticket->addAdminReply($this->request()['body'], $this->request()['guid']);
				if ($message->id_support_message) {
					Message_Incoming_Support::notifyReps($message->admin()->firstName() . ' replied to #' . $message->id_support . ': ' . $message->body, $message->support());
				}
			}
			if( $message ){
				echo $message->json();
				exit;
			}
		}

		header('HTTP/1.0 409 Conflict');
		exit;
	}
}