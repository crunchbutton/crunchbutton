<?php

class Controller_api_order_gid extends Crunchbutton_Controller_Rest {	
	public function init() {
		$order = Order::gid( c::getPagePiece(3) );
		if( $order->id_order ){
			echo json_encode( [ 'uuid' => $order->uuid ] );
		} else {
			echo json_encode(['error' => 'invalid object']);
		}
	}
}