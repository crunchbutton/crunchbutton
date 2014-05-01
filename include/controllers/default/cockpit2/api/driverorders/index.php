<?php

class Controller_api_driverorders extends Crunchbutton_Controller_RestAccount {
	
	public function init() {

		$lastHours = 12; // last 12 hours

		if( c::getPagePiece( 2 ) ){

			switch ( c::getPagePiece( 2 ) ) {
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
				
				default:
					$order = Order::o(c::getPagePiece( 2 ) );

					if ( $this->method() == 'post' ) {

						$res = [];

						switch ( c::getPagePiece(3) ) {
							case 'delivery-pickedup':
								$res['status'] = $order->deliveryPickedup(c::admin());
								break;

							case 'delivery-delivered':
								$res['status'] = $order->deliveryDelivered(c::admin());
								break;

							case 'delivery-accept':
								$res['status'] = $order->deliveryAccept(c::admin());
								break;

							case 'delivery-reject':
								$order->deliveryReject(c::admin());
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