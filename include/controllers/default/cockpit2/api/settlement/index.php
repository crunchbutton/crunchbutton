<?php

class Controller_api_settlement extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if( !c::admin()->permission()->check( ['global', 'settlement' ] ) ){
			$this->_error();
		}

		switch ($this->method()) {
			case 'get':
				switch ( c::getPagePiece( 2 ) ) {
					case 'range':
						$this->_range();
						break;
					default:
						$this->_error();
						break;
				}
				break;
			case 'post':
				switch ( c::getPagePiece( 2 ) ) {
					case 'begin':
						$this->_begin();
						break;
					default:
						$this->_error();
						break;
				}
				break;
		}
	}

	private function _begin(){

		$start = $this->request()['start'];
		$end = $this->request()['end'];
		$pay_type = ( $this->request()['pay_type'] == 'all' ) ? '' : $this->request()['pay_type'];

		if( !$start || !$end ){
			$this->_error();
		}

		$settlement = new Settlement( [ 'payment_method' => $pay_type, 'start' => $start, 'end' => $end ] );
		$restaurants = $settlement->start();
		$out = [ 'restaurants' => [] ];
		$total_restaurants = 0;
		$total_payments = 0;
		$total_orders = 0;
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

			$orders = [];
			foreach ( $_restaurant->_payableOrders as $_order ) {
				$order = [];
				$order[ 'id_order' ] = $_order->id_order;
				$order[ 'name' ] = $_order->name;
				$order[ 'pay_type' ] = $_order->pay_type;
				$order[ 'total' ] = $_order->final_price_plus_delivery_markup;
				$date = $_order->date();
				$order[ 'date' ] = $date->format( 'M jS Y g:i:s A' );
				$orders[] = $order;
			}
			$restaurant[ 'orders' ] = $orders;
			$restaurant[ 'orders_count' ] = count( $orders );
			if( floatval( $restaurant[ 'total_due' ] ) > 0 ){
				$out[ 'restaurants' ][] = $restaurant;
				$total_restaurants++;
				$total_orders += count( $orders );
				$total_payments += $restaurant[ 'total_due' ];
			}
		}
		$out[ 'total_restaurants' ] = $total_restaurants;
		$out[ 'total_payments' ] = $total_payments;
		$out[ 'total_orders' ] = $total_orders;
		echo json_encode( $out );
	}

	private function _range(){
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$range = [ 'end' => $now->format( 'Y/m/d' ) ];
		$now->modify( '-1 week' );
		$range[ 'start' ] = $now->format( 'Y/m/d' );
		echo json_encode( $range );
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}
