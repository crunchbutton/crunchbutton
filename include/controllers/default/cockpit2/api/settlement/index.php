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
							case 'reimburse-cash-order':
								$this->_restaurantReimburseCashOrder();
								break;
							case 'schedule':
								$this->_restaurantSchedule();
								break;
							case 'payments':
								$this->_restaurantPayments();
								break;
							case 'do-payment':
								$this->_restaurantDoPayment();
								break;
							case 'payment':
								$this->_restaurantPayment();
								break;
							case 'send-summary':
								$this->_restaurantSendSummary();
								break;
							case 'view-summary':
								$this->_restaurantViewSummary();
								break;
							case 'scheduled':
								$this->_restaurantScheduled();
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

	private function _restaurantDoPayment(){
		$id_payment_schedule = c::getPagePiece( 4 );
		$schedule = Cockpit_Payment_Schedule::o( $id_payment_schedule );
		if( $schedule->id_payment_schedule ){
			if( $schedule->status == Cockpit_Payment_Schedule::STATUS_DONE ){
				echo json_encode( [ 'error' => 'Payment already done!' ] );
				exit;
			}
			if( $schedule->status == Cockpit_Payment_Schedule::STATUS_PROCESSING ){
				echo json_encode( [ 'error' => 'Payment already in process!' ] );
				exit;
			}
			$settlement = new Settlement;
			if( $settlement->doRestaurantPayments( $id_payment_schedule ) ){
				echo json_encode( [ 'success' => true ] );
			} else {
				echo json_encode( [ 'error' => 'Problem finishing the payment!' ] );
			}
		} else {
			echo json_encode( [ 'error' => 'Payment schedule not found!' ] );
		}
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
		// default notes
		$out = [ 'notes' => Crunchbutton_Settlement::DEFAULT_NOTES ];
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
			$restaurant[ 'reimburse_cash_orders' ] = 0;

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
				$order[ 'reimburse_cash_order' ] = ( $_order->reimburse_cash_order ) ? true : false;
				$order[ 'pay_type' ] = ucfirst( $_order->pay_type );
				$order[ 'included' ] = ( !$_order->refunded ) ? true : ( $_order->refunded && $_order->pay_if_refunded ) ? true : false;
				if( !$order[ 'included' ] ){
					$restaurant[ 'not_included' ]++;
				}
				if( $order[ 'reimburse_cash_order' ] ){
					$restaurant[ 'reimburse_cash_orders' ]++;
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

	private function _restaurantPayIfRefunded(){
		$id_order = $this->request()['id_order'];
		$pay_if_refunded = $this->request()['pay_if_refunded'];
		$order = Order::o( $id_order );
		$order->pay_if_refunded = ( intval( $pay_if_refunded ) > 0 ) ? 1 : 0;
		$order->save();
		echo json_encode( [ 'id_order' => $order->id_order, 'id_restaurant' => $order->id_restaurant ] );
	}

	private function _restaurantReimburseCashOrder(){
		$id_order = $this->request()['id_order'];
		$reimburse_cash_order = $this->request()['reimburse_cash_order'];
		$order = Order::o( $id_order );
		$order->reimburse_cash_order = ( intval( $reimburse_cash_order ) > 0 ) ? 1 : 0;
		$order->save();
		echo json_encode( [ 'id_order' => $order->id_order, 'id_restaurant' => $order->id_restaurant ] );
	}

	private function _restaurantSchedule(){
		$start = $this->request()['start'];
		$end = $this->request()['end'];
		$_id_restaurants = explode( ',', $this->request()['id_restaurants'] );
		$id_restaurants = [];
		foreach ( $_id_restaurants as $key => $val ) {
			$id_restaurant = trim( $val );
			$notes = $this->request()[ 'notes_' . $id_restaurant ];
			$id_restaurants[ $id_restaurant ] = ( $notes ) ? $notes : Crunchbutton_Settlement::DEFAULT_NOTES;
		}
		$pay_type = ( $this->request()['pay_type'] == 'all' ) ? '' : $this->request()['pay_type'];
		$settlement = new Settlement( [ 'payment_method' => $pay_type, 'start' => $start, 'end' => $end ] );
		$settlement->scheduleRestaurantPayment( $id_restaurants );
		echo json_encode( [ 'success' => true ] );
	}

	private function _restaurantPayments(){

		$resultsPerPage = 20;

		$page = max( $this->request()['page'], 1 );
		$id_restaurant = max( $this->request()['id_restaurant'], 0 );
		$start = ( ( $page - 1 ) * $resultsPerPage );

		$payments = Crunchbutton_Payment::listPayments( [ 'limit' => $start . ',' . $resultsPerPage, 'id_restaurant' => $id_restaurant ] );
		$payments_total = Crunchbutton_Payment::listPayments( [ 'id_restaurant' => $id_restaurant ] );
		$payments_total = $payments_total->count();

		$list = [];
		foreach( $payments as $payment ){
			$data = $payment->exports();
			$data[ 'date' ] = $payment->date()->format( 'M jS Y g:i:s A' );
			unset( $data[ 'id_driver' ] );
			unset( $data[ 'note' ] );
			unset( $data[ 'notes' ] );
			unset( $data[ 'type' ] );
			unset( $data[ 'id' ] );
			$list[] = $data;
		}

		$pages = ceil( $payments_total / $resultsPerPage );

		$data = [];
		$data[ 'count' ] = $payments_total;
		$data[ 'pages' ] = $pages;
		$data[ 'prev' ] = ( $page > 1 ) ? $page - 1 : null;
		$data[ 'page' ] = intval( $page );
		$data[ 'next' ] = ( $page < $pages ) ? $page + 1 : null;
		$data[ 'results' ] = $list;

		echo json_encode( $data );
	}

	private function _restaurantSendSummary(){
		$id_payment_schedule = c::getPagePiece( 4 );
		$settlement = new Settlement;
		if( $settlement->sendRestaurantPaymentNotification( $id_payment_schedule ) ){
			echo json_encode( [ 'success' => true ] );
		} else {
			echo json_encode( [ 'error' => true ] );
		}
	}

	private function _restaurantPayment(){
		$settlement = new Settlement;
		$id_payment = c::getPagePiece( 4 );
		$summary = $settlement->restaurantSummaryByIdPayment( $id_payment );
		if( $summary ){
			echo json_encode( $summary );
		} else {
			$this->_error();
		}
	}

	private function _restaurantScheduled(){
		if( c::getPagePiece( 4 ) ){
			$settlement = new Settlement;
			$id_payment_schedule = c::getPagePiece( 4 );
			$summary = $settlement->restaurantSummary( $id_payment_schedule );
			if( $summary ){
				echo json_encode( $summary );
			} else {
				$this->_error();
			}
		} else {
			$schedule = new Cockpit_Payment_Schedule;
			$schedules = $schedule->restaurantNotCompletedSchedules();
			$out = [ 'last_date' => $lastDate, 'restaurants' => '', 'scheduled' => 0, 'processing' => 0, 'done' => 0, 'error' => 0, 'total' => 0 ];
			foreach( $schedules as $_schedule ){
				$data = $_schedule->exports();
				$data[ 'date' ] = $_schedule->date()->format( 'M jS Y g:i:s A' );
				$out[ 'restaurants' ][] = $data;
				$out[ $_schedule->status ]++;
				$out[ 'total' ]++;
			}
			$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
			$out[ 'updated_at' ] = $now->format( 'M jS Y g:i:s A' );
			echo json_encode( $out );
		}
	}

	public function _restaurantViewSummary(){
		$id_payment =  c::getPagePiece( 4 );
		$settlement = new Crunchbutton_Settlement;
		$summary = $settlement->restaurantSummaryByIdPayment( $id_payment );
		$mail = new Crunchbutton_Email_Payment_Summary( [ 'summary' => $summary ] );
		echo $mail->message();
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
		$range = [ 'end' => $now->format( 'Y,m,d' ) ];
		$now->modify( '-1 week' );
		$range[ 'start' ] = $now->format( 'Y,m,d' );
		echo json_encode( $range );
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}
