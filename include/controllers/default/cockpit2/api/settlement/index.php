<?php

class Controller_api_settlement extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if( !c::admin()->permission()->check( ['global', 'settlement' ] ) ){
			$this->_error();
		}

		switch ($this->method()) {
			case 'get':
				switch ( c::getPagePiece( 2 ) ) {
					case 'restaurants':
						switch ( c::getPagePiece( 3 ) ) {
							case 'range':
								$this->_range();
								break;
							default:
								$this->_error();
								break;
						}
						break;
					case 'drivers':
						switch ( c::getPagePiece( 3 ) ) {
							case 'range':
								$this->_range();
								break;
							default:
								$this->_error();
								break;
						}
						break;
					default:
						$this->_error();
						break;
				}
				break;
			case 'post':
				switch ( c::getPagePiece( 2 ) ) {
					case 'restaurants':
						switch ( c::getPagePiece( 3 ) ) {
							case 'begin':
								$this->_restaurantBegin();
								break;
							case 'restaurant':
								$this->_restaurantBegin();
								break;
							case 'pay-if-refunded':
								$this->_restaurantPayIfRefunded();
								break;
							default:
								$this->_error();
								break;
						}
						break;
					case 'drivers':
						switch ( c::getPagePiece( 3 ) ) {
							case 'begin':
								$this->_driverBegin();
								break;
							default:
								$this->_error();
								break;
						}
						break;
					default:
						$this->_error();
						break;
				}
				break;
		}
	}

	private function _restaurantPayIfRefunded(){
		$id_order = $this->request()['id_order'];
		$pay_if_refunded = $this->request()['pay_if_refunded'];
		$order = Order::o( $id_order );
		$order->pay_if_refunded = ( intval( $pay_if_refunded ) > 0 ) ? 1 : 0;
		$order->save();
		echo json_encode( [ 'id_order' => $order->id_order, 'id_restaurant' => $order->id_restaurant ] );
	}

	private function _restaurantBegin(){

		$start = $this->request()['start'];
		$end = $this->request()['end'];
		$id_restaurant = $this->request()['id_restaurant'];
		$pay_type = ( $this->request()['pay_type'] == 'all' ) ? '' : $this->request()['pay_type'];

		if( !$start || !$end ){
			$this->_error();
		}

		$settlement = new Settlement( [ 'payment_method' => $pay_type, 'start' => $start, 'end' => $end, 'id_restaurant' => $id_restaurant ] );
		$restaurants = $settlement->startRestaurant();
		$out = [ 'restaurants' => [] ];
		foreach ( $restaurants as $_restaurant ) {
			$restaurant = $_restaurant->payment_data;
			$lastPayment = $_restaurant->getLastPayment();
			if( $lastPayment->id_payment ){
				$_lastPayment = [];
				$_lastPayment[ 'amount' ] = $lastPayment->amount;
				$_lastPayment[ 'date' ] = $lastPayment->date()->format( 'M jS Y g:i:s A' );
				$_lastPayment[ 'id_payment' ] = $lastPayment->id_payment;
				$restaurant[ 'last_payment' ] = $_lastPayment;
			}
			$restaurant[ 'name' ] = $_restaurant->name;
			$restaurant[ 'id_restaurant' ] = $_restaurant->id_restaurant;
			$restaurant[ 'not_included' ] = 0;
			$restaurant[ 'orders_count' ] = 0;

			if( $id_restaurant && $id_restaurant == $restaurant[ 'id_restaurant' ] ){
				$restaurant[ 'show_orders' ] = true;
			}
			$orders = [];
			foreach ( $_restaurant->_payableOrders as $_order ) {
				$order = [];
				$order[ 'id_order' ] = $_order->id_order;
				$order[ 'name' ] = $_order->name;
				$order[ 'refunded' ] = ( $_order->refunded ) ? true : false;
				$order[ 'pay_if_refunded' ] = ( $_order->pay_if_refunded ) ? true : false;
				$order[ 'pay_type' ] = ucfirst( $_order->pay_type );
				$order[ 'included' ] = ( !$_order->refunded ) ? true : ( $_order->refunded && $_order->pay_if_refunded ) ? true : false;
				if( !$order[ 'included' ] ){
					$restaurant[ 'not_included' ]++;
				}
				$order[ 'total' ] = $_order->final_price_plus_delivery_markup;
				$date = $_order->date();
				$order[ 'date' ] = $date->format( 'M jS Y g:i:s A' );
				$orders[] = $order;
				$restaurant[ 'orders_count' ]++;
			}
			$restaurant[ 'pay' ] = true;
			$restaurant[ 'orders' ] = $orders;
			if( floatval( $restaurant[ 'total_due' ] ) > 0 ){
				$out[ 'restaurants' ][] = $restaurant;
				$total_restaurants++;
				$total_orders += count( $orders );
				$total_payments += $restaurant[ 'total_due' ];
			}
		}
		echo json_encode( $out );
	}

	private function _driverBegin(){

		$start = $this->request()['start'];
		$end = $this->request()['end'];
		$pay_type = ( $this->request()['pay_type'] == 'all' ) ? '' : $this->request()['pay_type'];

		if( !$start || !$end ){
			$this->_error();
		}

		$settlement = new Settlement( [ 'payment_method' => $pay_type, 'start' => $start, 'end' => $end ] );
		$orders = $settlement->startDriver();
		$out = [ 'drivers' => [] ];
		$total_drivers = 0;
		$total_payments = 0;
		$total_orders = 0;
		foreach ( $orders as $key => $val ) {
			if( !$orders[ $key ][ 'name' ] ){
				continue;
			}
			$driver = $orders[ $key ];
			$total_drivers++;
			unset( $driver[ 'orders' ] );
			$driver[ 'orders' ] = [];
			foreach( $orders[ $key ][ 'orders' ] as $order ){
				$_order = [];
				$_order[ 'id_order' ] = $order[ 'id_order' ];
				$_order[ 'name' ] = $order[ 'name' ];
				$_order[ 'restaurant' ] = $order[ 'restaurant' ];
				$_order[ 'pay_type' ] = ucfirst( $order[ 'pay_type' ] );
				$_order[ 'total' ] = $order[ 'final_price_plus_delivery_markup' ];
				$_order[ 'date' ] = $order[ 'date' ];
				$driver[ 'orders' ][] = $_order;
				$total_orders++;
			}
			$driver[ 'orders_count' ] = count( $driver[ 'orders' ] );
			$out[ 'drivers' ][] = $driver;
			$total_payments += $driver[ 'total_due' ];
		}
		$out[ 'total_drivers' ] = $total_drivers;
		$out[ 'total_payments' ] = $total_payments;
		$out[ 'total_orders' ] = $total_orders;
		echo json_encode( $out );
	}

	private function _range(){
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$range = [ 'end' => '2014,05,10' /*$now->format( 'Y,m,d' ) */ ];
		$now->modify( '-1 week' );
		$range[ 'start' ] = '2014,05,04' /*$now->format( 'Y,m,d' )*/;
		echo json_encode( $range );
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}
