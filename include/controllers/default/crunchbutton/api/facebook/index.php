<?php

class Controller_api_facebook extends Crunchbutton_Controller_Rest {

public function init() {

		switch ($this->method()) {
			case 'get':

			switch ( c::getPagePiece( 2 ) ) {
				// url
				case 'url':
					$facebook = new Crunchbutton_Facebook();
					echo json_encode(['url' => $facebook->getLoginURL() ]);
					break;

				// status
				case 'status' :
					$facebook = new Crunchbutton_Facebook();
					switch ( c::getPagePiece( 3 ) ) {
							// status/order/x
							case 'order':
								$uuid = c::getPagePiece( 4 );
								if( $uuid ){
									$status = $facebook->getOrderStatus( $uuid );
									if ( $status ){
										echo json_encode(['success' => $status]);
									} else {
										// @todo catch the error
										echo json_encode(['error' => 'invalid resource']);
									}
								} else {
									echo json_encode(['error' => 'invalid resource']);
								}
							break;
					}

				break;

				// publish
				case 'publish':
					$facebook = new Crunchbutton_Facebook();
					if( !$facebook->isLogged() ){
						echo json_encode(['error' => 'not logged']);
						exit;
					}

					if( $facebook->hasPublishPermission() ){
						switch ( c::getPagePiece( 3 ) ) {
							// permission
							case 'permission':
								if( $facebook->hasPublishPermission() ){
									echo json_encode(['success' => 'has permission']);
								} else {
									echo json_encode(['error' => 'not allowed', 'url' => $facebook->getLoginURL() ]);
								}
								break;
								default:
									echo json_encode(['error' => 'invalid resource']);
								break;
						}

					} else {
						echo json_encode(['error' => 'not allowed', 'url' => $facebook->getLoginURL() ]);
					}
					break;
			}

			break;

			case 'post':
				switch ( c::getPagePiece( 2 ) ) {

					case 'reward':
						$post_id = $this->request()[ 'post_id' ];
						$uuid = $this->request()[ 'uuid' ];
						$order = Order::uuid( $uuid );
						if( c::user()->id_user == $order->id_user && $post_id ){
							$reward = new Crunchbutton_Reward;
							$points = $reward->sharedOrder( $order->id_order );
							if( floatval( $points ) > 0 ){
								if( !$reward->orderWasAlreadyShared( $order->id_order ) ){
									$reward->saveReward( [  'id_user' => $order->id_user, 'id_order' => $order->id_order, 'points' => $points, 'note' => 'points by sharing order' ] );
									echo json_encode(['success' => 'success']);
									exit;
								}
							}
						}
						echo json_encode(['error' => 'error']);
					break;

					// url auth
					case 'token':
						$facebook = new Crunchbutton_Facebook();
						$facebook->setToken( $_POST[ 'token' ] );
						echo json_encode(['success' => 'success']);
						break;

					break;

					case 'publish':

						switch ( c::getPagePiece( 3 ) ) {

							// publish/order/x
							case 'order':
								$facebook = new Crunchbutton_Facebook();
								$uuid = $_POST[ 'uuid' ];
								if( $uuid ){
									if ( $facebook->postOrderStatus( $uuid ) ){
										echo json_encode(['success' => 'status posted']);
									} else {
										// @todo catch the error
										echo json_encode(['error' => 'invalid resource']);
									}
								} else {
									echo json_encode(['error' => 'invalid resource']);
								}
							break;
						}
					break;
				}

			break;
			default:
				echo json_encode(['error' => 'invalid resource']);
			break;
		}
	}

}