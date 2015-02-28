<?php

class Controller_home extends Crunchbutton_Controller_Account {
	public function __construct() {
		$this->cbtn_forward = c::config()->site->config('cbtn_forward')->val() == 'true' ? true : false;

		if ($this->cbtn_forward) {
			$p = c::getPagePiece(0);
			switch ($p) {
				case 'shifts-list':
					$url = 'drivers/shifts';
					break;
				case 'orders-list':
					$url = 'drivers/orders';
					break;
				default:
					if ($p) {
						$url = 'drivers/order/'.$p;
					}
					break;
			}
			header('Location: https://cockpit.la/'.$url);
			exit;
		}
		
		parent::__construct();
	}

	public function init() {
		c::view()->layout( 'layout/html' );
		
		if (c::getPagePiece(0)) {
			// show the order
			$order = Order::o(c::getPagePiece(0));
			if ( $order->id_order ) {
				c::view()->order = $order;
				c::view()->display('order/index');
				exit();
			} else {
				$this->miniRouter();
			}
		} else {
			$this->miniRouter();
		}
	}

	public function miniRouter(){
		c::view()->menu = true;
		switch ( c::getPagePiece( 0 ) ) {
			case 'shifts-list':
				switch ( c::getPagePiece( 1 ) ) {
					case 'all':
						$this->showAllShifts();
						break;
					case 'mine':
					default:
						$this->showMineShifts();
						break;
				}
				break;
			case 'orders-list':
			default:
				switch ( c::getPagePiece( 1 ) ) {
					case 'mine':
						$this->showMineOrders();
						break;
					case 'all':
					default:
						$this->showAllOrders();
						break;
				}
				break;
		}
	}

	public function showAllShifts(){
		c::view()->actual = 'shifts-all';
		$communities = c::admin()->communitiesHeDeliveriesFor();
		$list = [];
		foreach( $communities as $community ){
			$list[] = $community->id_community;
		}
		c::view()->shifts = Crunchbutton_Community_Shift::nextShiftsByCommunities( $list );
		c::view()->display( 'shifts/all' );
	}

	public function showMineShifts(){
		c::view()->actual = 'shifts-mine';
		c::view()->shifts = Crunchbutton_Community_Shift::nextShiftsByAdmin( c::admin()->id_admin );
		c::view()->display( 'shifts/mine' );
	}

	public function showAllOrders(){
		c::view()->actual = 'orders-all';
		c::view()->orders = $this->getOrdersList( true );
		c::view()->display( 'orders/index' );
	}

	public function showMineOrders(){
		c::view()->actual = 'orders-mine';
		c::view()->orders = $this->getOrdersList();
		c::view()->display( 'orders/index' );
	}
	
	public function getOrdersList( $all = false ){
		$hours = ( $_GET[ 'hours' ] ) ? $_GET[ 'hours' ] : 12;
		$orders = Order::deliveryOrders( $hours );

		$justMineOrders = !$all;

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

		return $list;
	}

}