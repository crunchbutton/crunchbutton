<?php

class Controller_api_twitter extends Crunchbutton_Controller_Rest {

public function init() {

		switch ($this->method()) {

			case 'post':
				switch ( c::getPagePiece( 2 ) ) {
					case 'reward':
						$uuid = $this->request()[ 'uuid' ];
						$order = Order::uuid( $uuid );
						if( c::user()->id_user == $order->id_user ){
							$reward = new Crunchbutton_Reward;
							$points = $reward->sharedOrder( $order->id_order, 'twitter' );
							if( floatval( $points ) > 0 ){
								if( !$reward->orderWasAlreadySharedTwitter( $order->id_order ) ){
									$reward->saveReward( [  'id_user' => $order->id_user, 'id_order' => $order->id_order, 'points' => $points, 'note' => 'twitter shared', 'shared' => 'twitter' ] );
									echo json_encode(['success' => 'success']);
									exit;
								}
							}
						}
						echo json_encode(['error' => 'error']);
					break;
				}

			break;
			default:
				echo json_encode(['error' => 'invalid resource']);
			break;
		}
	}

}