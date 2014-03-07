<?php

class Controller_home extends Crunchbutton_Controller_Account {
	
	public function init() {

		c::view()->layout( 'layout/html' );
		
		if( c::db()->escape( c::getPagePiece( 0 ) ) ){
			// show the order
			$order = Order::o( c::db()->escape( c::getPagePiece( 0 ) ) );
			if ( $order->id_order ) {
				c::view()->order = $order;
				c::view()->display('order/index');
				exit();
			} else {
				$this->showList();
			}
		} else {
			$this->showList();
		}
	}

	public function showList(){

		$justMineOrders = ( c::db()->escape( c::getPagePiece( 0 ) ) == 'mine' );

		$hours = c::getPagePiece( 1 ) ? c::getPagePiece( 1 ) : 12;

		$orders = Order::deliveryOrders( $hours, ( c::db()->escape( c::getPagePiece( 0 ) ) == 'all' ) );

		$list = [];
		foreach ( $orders as $order ) {
			$order =  (object) array(
										'id_order' => $order->id_order,
										'lastStatus' => $order->deliveryLastStatus(),
										'name' => $order->name,
										'phone' => $order->phone,
										'date' => $order->date(),
										'restaurant' => $order->restaurant()->name,
										);
			if( !$justMineOrders || ( $justMineOrders && $order->lastStatus[ 'id_admin' ] == c::admin()->id_admin ) ){
				$list[] = $order;
			}
		}

		usort( $list, function( $a, $b ){
			if( $a->lastStatus[ 'status' ] == $b->lastStatus[ 'status' ] ){
				return $a->id_order < $b->id_order;
			}
			return ( $a->lastStatus[ 'order' ] > $b->lastStatus[ 'order' ] );
		} );

		c::view()->justMineOrders = $justMineOrders;
		c::view()->hours = $hours;
		c::view()->orders = $list;
		c::view()->display( 'home/index' );
	}

}