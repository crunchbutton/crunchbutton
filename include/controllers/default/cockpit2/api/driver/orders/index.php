<?php

class Controller_api_driver_orders extends Crunchbutton_Controller_RestAccount {

	public function init() {

		$lastHours = 12; // last 12 hours

		if( c::getPagePiece( 3 ) ){

			switch ( c::getPagePiece( 3 ) ) {


				case 'revenue':
					$id_admin = c::admin()->id_admin;
					$revenueCurrentShift = Admin::revenueCurrentShift( $id_admin );
					$revenueLastShift = Admin::revenueLastWorkedShift( $id_admin );
					echo json_encode( ['totalCurrent' => $revenueCurrentShift,
									   'totalLast' => $revenueLastShift] );
					break;

				case 'undelivered':
					$count = 0;
					$orders = Order::outstandingOrders();
					foreach ( $orders as $order ) {
						$count++;
					}
					echo json_encode( [ 'total' => $count ] );
				break;

				//To be continued
				case 'times':
					$id_admin = c::admin()->id_admin;
					$avgTimeLastShift = Admin::avgDeliveryTimeLastShift( $id_admin );
					$avgTimeCurrentShift = Admin::avgDeliveryTimeCurrentShift( $id_admin );
					$orderCountLastShift = Admin::numberOfDeliveredOrdersLastShift( $id_admin );
					$orderCountCurrentShift = Admin::numberOfDeliveredOrdersCurrentShift( $id_admin );
					echo json_encode( [ 'total_last' => $avgTimeLastShift,
									'total_current' => $avgTimeCurrentShift,
									'orderCountLast' => $orderCountLastShift,
									'orderCountCurrent' => $orderCountCurrentShift ] );
					break;

				default:

					$order = Order::o(c::getPagePiece( 3 ) );
					// Test order #2969 - step 3
					if( $order->id_order == Cockpit_Driver_Notify::ORDER_TEST ){
						$last = Crunchbutton_Order_Action::byOrder( $order->id_order );
						// delete last actions so the driver could play with
						if( $last->id_order_action && c::user()->id_admin != $last->id_admin && $last->type != Crunchbutton_Order_Action::DELIVERY_REJECTED ){
							c::db()->query( 'DELETE FROM order_action WHERE id_order = "' . $order->id_order . '"' );
						}
					}


					if ( $this->method() == 'post' ) {

						$res = [];

						switch ( c::getPagePiece(4) ) {
							case 'delivery-pickedup':
								$res['status'] = $order->deliveryPickedup(c::user());
								break;

							case 'delivery-delivered':
								$res['status'] = $order->deliveryDelivered(c::user());
								break;

							case 'delivery-accept':
								$res['status'] = $order->deliveryAccept(c::user(), true);
								break;

							case 'delivery-reject':
								$order->deliveryReject(c::user());
								$res['status'] = true;
								break;

							case 'undo-accepted':
								$res[ 'status' ] = $order->undoStatus('accepted');
								break;

							case 'undo-delivered':
								$res[ 'status' ] = $order->undoStatus('delivered');
								break;

							case 'undo-pickedup':
								$res[ 'status' ] = $order->undoStatus('pickedup');
								break;
						}

						if ( $order->deliveryStatus() ){
							$ret = $order->deliveryExports();
						}
						$ret[ 'status' ] = $res[ 'status' ];

						echo json_encode( $ret );
						exit;
					} else {

						if( $order->id_order ) {
							echo $order->json();
						} else {
							echo json_encode(['error' => 'invalid object']);
						}
					}
					break;
			}

		} else {

			$exports = [];

			// @demo
			$orders = Order::deliveryOrders( $lastHours );

			foreach ( $orders as $order ) {
				$exports[] = Model::toModel( [
					'id_order' => $order->id_order,
					'lastStatus' => $order->deliveryLastStatus(),
					'name' => $order->name,
					'phone' => $order->phone,
					'date' => $order->date(),
					'date_hour' => $order->date()->format( 'g:i A'),
					'restaurant' => $order->restaurant()->name,
				] );
			}

			usort( $exports, function( $a, $b ){
				if( $a->lastStatus->status == $b->lastStatus->status ){
					return $a->id_order < $b->id_order;
				}
				return ( $a->lastStatus->order > $b->lastStatus->order );
			} );

			echo json_encode($exports);
		}
	}
}