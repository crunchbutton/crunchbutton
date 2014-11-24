<?php

class Controller_api_order extends Crunchbutton_Controller_RestAccount {

	public function init() {

		switch ($this->method()) {

			case 'get':
				switch ( c::getPagePiece( 2 ) ) {

					case 'restaurant-list-last':

						if( is_numeric( c::getPagePiece( 3 ) ) && c::admin()->permission()->check( [ 'global' ] ) ){
							$restaurant = Restaurant::o( intval( c::getPagePiece( 3 ) ) );
						}

						if( !$restaurant->id_restaurant ){
							$restaurant = Admin::restaurantOrderPlacement();
						}
						if( $restaurant->id_restaurant ){
							$out = [ 'id_restaurant' => intval( $restaurant->id_restaurant ), 'orders' => [] ];
							$orders = Order::q( 'SELECT * FROM `order` o WHERE id_restaurant = "' . $restaurant->id_restaurant . '" AND o.date BETWEEN NOW() - INTERVAL 7 DAY AND NOW() ORDER BY id_order DESC' );
							foreach( $orders as $order ) {
								$out[ 'orders' ][]	= array( 	'id_order' => $order->id_order,
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

						if (!$order->id_order) {
							$order = Order::o(c::getPagePiece(2));
						}

						if (get_class($order) != 'Cockpit_Order') {
							$order = $order->get(0);
						}

						if (!$order->id_order) {
							header('HTTP/1.0 404 Not Found');
							exit;
						}
						
						$restaurant = Admin::restaurantOrderPlacement();
						
						if (!c::admin()->permission()->check(['global','orders-all','orders-list-page']) && $restaurant->id_restaurant != $order->id_restaurant) {
							header('HTTP/1.1 401 Unauthorized');
							exit;
						}
						
						switch (c::getPagePiece(3)) {
							case 'eta':
								echo json_encode(['time' => $order->eta()]);
								break;

							case 'status':
								echo json_encode($order->status()->last());
								break;

							default:
								$out = $order->exports();
								$out['user'] = $order->user()->id_user ? $order->user()->exports() : null;
								$out['restaurant'] = $order->restaurant()->id_restaurant ? $order->restaurant()->exports() : null;
		
								echo json_encode($out);
								break;
						}

						break;
				}
				break;

			case 'post':

				if( is_numeric( $_POST[ 'restaurant' ] ) && c::admin()->permission()->check( [ 'global' ] ) ){
					$restaurant = Restaurant::o( intval( $_POST[ 'restaurant' ] ) );
				}

				if( !$restaurant->id_restaurant ){
					$restaurant = Admin::restaurantOrderPlacement();
				}

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