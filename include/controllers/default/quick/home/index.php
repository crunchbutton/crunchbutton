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

		c::view()->orders = Order::deliveryOrders();

		c::view()->display( 'home/index' );
	}

}