<?php

class Controller_api_driverorders extends Crunchbutton_Controller_RestAccount {
	
	public function init() {

		if( c::getPagePiece( 2 ) ){

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

				$order = Order::o( c::getPagePiece( 2 ) );
				if( $order->id_order ) {
					echo $order->json();
				} else {
					echo json_encode(['error' => 'invalid object']);
				}
			}

		} else {

			$exports = [];

			$orders = Order::deliveryOrders( 12 ); // last 12 hours
			
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