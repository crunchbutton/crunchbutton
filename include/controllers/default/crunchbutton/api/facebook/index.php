<?php

class Controller_api_facebook extends Crunchbutton_Controller_Rest {

public function init() {
		
		switch ($this->method()) {
			case 'get':

			switch ( c::getPagePiece( 2 ) ) {
				
				case 'publish_permission':

					$facebook = new Crunchbutton_Facebook();

					if( $facebook->hasPublishPermission() ){
						echo json_encode(['success' => 'has permission']);

					} else {
						echo json_encode(['error' => 'not allowed', 'url' => $facebook->getLoginURL() ]);
					}

					break;
				// publish
				case 'publish':

					$facebook = new Crunchbutton_Facebook();

					if( $facebook->hasPublishPermission() ){
						
						switch ( c::getPagePiece( 3 ) ) {

							// publish/order/x
							case 'order':

								$order_id = c::getPagePiece( 4 );
								
								if( $order_id ){
									
									$facebook->postOrderStatus( $order_id );
									echo json_encode(['success' => 'status posted']);

								} else {
									
									echo json_encode(['error' => 'invalid resource']);
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

			default:
				echo json_encode(['error' => 'invalid resource']);
			break;
		}
	}

}