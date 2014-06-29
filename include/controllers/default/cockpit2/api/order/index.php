<?php

class Controller_api_order extends Crunchbutton_Controller_RestAccount {

	public function init() {

		switch ($this->method()) {

			case 'get':
				switch ( c::getPagePiece( 2 ) ) {

					case 'list':

						$restaurant = Admin::restaurantOrderPlacement();
						if( $restaurant->id_restaurant ){
							$out = [];
							$orders = Order::q( 'SELECT * FROM `order` WHERE id_restaurant = "' . $restaurant->id_restaurant . '" ORDER BY id_order DESC LIMIT 20' );
							foreach( $orders as $order ) {
								$out[]	= array( 	'id_order' => $order->id_order,
																	'lastStatus' => $order->deliveryLastStatus(),
																	'name' => $order->name,
																	'phone' => $order->phone,
																	'date' => $order->date()->format( 'M jS Y g:i:s A' ),
															);
							}
							echo json_encode( $out );
						} else {
							echo json_encode(['error' => 'invalid object']);
						}

						break;

					default:

						$order = Order::uuid(c::getPagePiece(2));
						/* @var $order Crunchbutton_Order */
						if (!$order->id_order) {
							$order = Order::o(c::getPagePiece(2));
						}

						if (get_class($order) != 'Cockpit_Order') {
							$order = $order->get(0);
						}

						if( $order->id_order ){
							$restaurant = Admin::restaurantOrderPlacement();
							if( $restaurant && $restaurant->id_restaurant && $order->id_restaurant == $order->id_restaurant ){
								echo $order->json();
							} else {
								echo json_encode(['error' => 'invalid object']);
							}
						}

						break;
				}
				break;

			case 'post':

				$restaurant = Admin::restaurantOrderPlacement();
				if( $restaurant && $restaurant->id_restaurant && $_POST[ 'restaurant' ] == $restaurant->id_restaurant ){
					$order = new Order;
					// card, subtotal, tip, name, phone, address
					$charge = $order->process( $_POST, 'restaurant' );
					if ($charge === true) {
						echo json_encode([
							'id_order' => $order->id_order,
							'id_user' => $order->user()->id_user,
							'final_price' => $order->final_price,
							'uuid' => (new Order($order->id_order))->uuid
						]);
					} else {
						echo json_encode(['status' => 'false', 'errors' => $charge]);
						exit;
					}
				} else {
					echo json_encode(['status' => 'false', 'errors' => 'invalid request' ] );
					exit;
				}
				break;
		}
	}
}