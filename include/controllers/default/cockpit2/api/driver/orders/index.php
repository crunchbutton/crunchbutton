<?php

class Controller_api_driver_orders extends Crunchbutton_Controller_RestAccount {

	public function init() {

		$lastHours = 12; // last 12 hours

		if( c::getPagePiece( 3 ) ){

			switch ( c::getPagePiece( 3 ) ) {
				case 'count':
					$count = 0;
					$orders = Order::deliveryOrders( $lastHours );
					foreach ( $orders as $order ) {
						$status = $order->deliveryLastStatus();
						if( $status[ 'status' ] == 'new' ){
							$count++;
						}
					}
					echo json_encode( [ 'total' => $count ] );
					break;
				
				case 'accepted':
					$count = 0;
					$orders = Order::deliveryOrders( $lastHours );
					foreach ( $orders as $order ) {
						$status = $order->deliveryLastStatus();
						if( $status[ 'status' ] == 'accepted' ) {
							$count++;
						}
					}
					echo json_encode( [ 'total' => $count ] );
					break;
					
				case 'pickedup':
					$count = 0;
					$orders = Order::deliveryOrders( $lastHours );
					foreach ( $orders as $order ) {
						$status = $order->deliveryLastStatus();
						if( $status[ 'status' ] == 'pickedup' ) {
							$count++;
						}
					}
					echo json_encode( [ 'total' => $count ] );
					break;
					
				case 'revenue':
					$tips = 0;
					$deliveryFee = 0;
					$totalAmount = 0;
					$count = 0;
					$orders = Order::deliveryOrders( $lastHours );
					foreach ( $orders as $order ) {
						$totalAmount = $totalAmount + $order->deliveryFee() + $order->tip();
						$tips = $tips + $order->tip();
						$deliveryFee = $deliveryFee + $order->deliveryFee();
						$count++;
					
					}
					echo json_encode( ['total' => $totalAmount,
									   'delivery' => $deliveryFee,
									   'tips' => $tips,
									   'orders' => $count ] );
					break;
				
				case 'undelivered':
					$count = 0;
					$orders = Order::deliveryOrders( $lastHours );
					foreach ( $orders as $order ) {
						$status = $order->deliveryLastStatus();
						if( $status[ 'status' ] != 'delivered' ) {
							$count++;
						}
					}
					echo json_encode( [ 'total' => $count ] );
				break;
				
				//To be continued
				case 'times':
				
					// ID ADMIN
					$id_admin = 80;

					$total_delivered_orders = Crunchbutton_Order_Action::q( 'SELECT COUNT(*) AS total FROM order_action AS oa WHERE oa.id_admin = ' . $id_admin . ' AND oa.type = "delivery-delivered"' )->total;
					$total_shifts = Crunchbutton_Admin_Shift_Assign::q( 'SELECT COUNT(*) AS total FROM admin_shift_assign AS asa INNER JOIN community_shift AS cs ON cs.id_community_shift = asa.id_community_shift WHERE asa.id_admin = ' . $id_admin . ' AND cs.date_start < NOW()' )->total;
					$avg = round( $total_delivered_orders / $total_shifts ) ;
					
					echo json_encode( ['average' => $avg,
									   'totalshifts' => $total_shifts,
									   'totaldelivered' => $total_delivered_orders ] );
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
								$res['status'] = $order->deliveryAccept(c::user());
								break;

							case 'delivery-reject':
								$order->deliveryReject(c::user());
								$res['status'] = true;
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

			$orders = Order::deliveryOrders( $lastHours );

			foreach ( $orders as $order ) {
				$exports[] = Model::toModel( [
					'id_order' => $order->id_order,
					'lastStatus' => $order->deliveryLastStatus(),
					'name' => $order->name,
					'phone' => $order->phone,
					'date' => $order->date(),
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