<?php

class Controller_api_ticket extends Crunchbutton_Controller_RestAccount {

	public function init() {
		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud'])) {
			$this->error(401, true);
		}

		// Creates a new ticket for a certain order
		if (c::getPagePiece(2) == 'create' && $this->method() == 'post' && $this->request()['id_order']) {
			$order = Order::o($this->request()['id_order']);
			if (!$order->id_order) {
				$this->error(404, true);
			}
			$support = $order->getSupport(true);
			echo $support->json();
			exit;
		}

		$ticket = Support::o( c::getPagePiece( 2 ) );

		if (!$ticket->id_support) {
			$this->error(404, true);
		}

		if (get_class($ticket) != 'Crunchbutton_Support') {
			$ticket = $ticket->get(0);
		}

		if ($this->method() == 'get') {
			switch ( c::getPagePiece( 3 ) ) {

				case 'side-info':

					$page = c::getPagePiece( 4 );

					$data = $ticket->exportMessages( [ 'messages_page' => $page ] );

					$out = [];

					$out[ 'id_support' ] = $ticket->id_support;
					$out[ 'status' ] = $ticket->status;

					$out[ 'messages' ][ 'total' ] = $data[ 'total_messages' ];
					$out[ 'messages' ][ 'list' ] = $data[ 'messages' ];

					echo json_encode( $out );exit;
					break;

				default:
					$out = $ticket->exportsPage( 'ticket' );
					echo json_encode( $out );exit;
					break;
			}
		}

		if (c::getPagePiece(3) == 'open-close' && $this->method() == 'post' ) {
			if( $ticket->status == Crunchbutton_Support::STATUS_CLOSED ){
				$ticket->status = Crunchbutton_Support::STATUS_OPEN;
				$ticket->save();
				$ticket->addSystemMessage( c::admin()->name . ' opened this ticket.', false, true );
			} else {
				$ticket->status = Crunchbutton_Support::STATUS_CLOSED;
				$ticket->save();
				$ticket->addSystemMessage( c::admin()->name . ' closed this ticket.', false, true );
			}
			$ticket->emitStatusChanged();
			echo json_encode( [ 'success' => true ] );exit;
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

		$this->error(409, true);
	}

}
