<?php

class Controller_api_refund extends Crunchbutton_Controller_RestAccount {

	public function init() {
		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud'])) {
			header('HTTP/1.1 401 Unauthorized');
			exit;
		}

		switch ( c::getPagePiece( 2 ) ) {
			case 'check':
				$order = Order::uuid( c::getPagePiece( 3 ) );
				if( $order->id_order ){
					$order = $order->get( 0 );
					if( $order->checkIfOrderWasRefunded() ){
						echo json_encode(['status' => 'success']);
					} else {
						echo json_encode(['status' => 'false']);
					}
				} else {
					echo json_encode(['status' => 'false', 'errors' => 'invalid order id' ]);
				}
				return;
				break;

			default:
					$order = Order::uuid( c::getPagePiece( 2 ) );
					if( $order->id_order ){
						$status = $order->refund();
					if( $status ){
							echo json_encode(['status' => 'success']);
						} else {
							echo json_encode(['status' => 'false', 'errors' => $status->errors]);
						}
					} else {
						echo json_encode(['status' => 'false', 'errors' => 'invalid order id' ]);
					}
				break;
		}
	}
}