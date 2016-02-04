<?php

class Controller_Api_Settlement extends Crunchbutton_Controller_RestAccount {

	public function init() {

		$this->resultsPerPage = 20;

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
							case 'view-summary':
								$this->_restaurantViewSummary();
								break;
							case 'download-summary':
								$this->_restaurantDownloadSummary();
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
							case 'view-summary':
								$this->_driverViewSummary();
								break;
							case 'download-summary':
								$this->_driverDownloadSummary();
								break;
							case 'info-payroll':
								$this->_infoPayRoll();
								break;
							default:
								$this->_error();
								break;
						}
						break;
					case 'list':
						$this->_paymentList();
						break;
					case 'queue':
						$this->_queueList();
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
							case 'payment-status':
								$this->_restaurantCheckPaymentStatus();
								break;
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
							case 'do-not-pay-restaurant':
								$this->_restaurantDoNotPayForOrder();
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
							case 'download-summary':
								$this->_restaurantDownloadSummary();
								break;
							case 'scheduled':
								$this->_restaurantScheduled();
								break;
							case 'schedule-arbitrary-payment':
								$this->_restaurantScheduleArbitraryPayment();
								break;
							default:
								$this->_error();
								break;
						}
						break;
					case 'drivers':
						switch ( c::getPagePiece( 3 ) ) {
							case 'payment-status':
								$this->_driverCheckPaymentStatus();
								break;
							case 'begin':
								$this->_driverBegin();
								break;
							case 'do-not-pay-driver':
								$this->_driverDoNotPayForOrder();
								break;
							case 'transfer-driver':
								$this->_driverTransferDeliveryDriver();
								break;
							case 'do-payment':
								$this->_driverDoPayment();
								break;
							case 'force-payment':
								$this->_driverDoForcePayment();
								break;

							case 'old-payments':
								$this->_driverOldPayments();
								break;
							case 'schedule':
								$this->_driverSchedule();
								break;
							case 'scheduled':
								$this->_driverScheduled();
								break;
							case 'change-status':
								$this->_driverChangeStatus();
								break;
							case 'deleted':
								$this->_driverDeleted();
								break;
							case 'archived':
								$this->_driverArchived();
								break;
							case 'payments':
								$this->_driverPayments();
								break;
							case 'payment':
								$this->_driverPayment();
								break;
							case 'view-summary':
								$this->_driverViewSummary();
								break;
							case 'schedule-arbitrary-payment':
								$this->_driverScheduleArbitraryPayment();
								break;
							case 'send-summary':
								$this->_driverSendSummary();
								break;
							case 'send-summary':
								$this->_driverSendSummary();
								break;
							case 'do-err-payments':
								$this->_driverDoErrPayments();
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

			// Get payment type
			$payment_type = $_restaurant->payment_type();
			$summary_error = false;
			switch ( $payment_type->summary_method ) {
				case Crunchbutton_Restaurant_Payment_Type::SUMMARY_METHOD_EMAIL:
						if( !$payment_type->summary_email ){
							$summary_error = 'Summary email missing';
						}
					break;
				case Crunchbutton_Restaurant_Payment_Type::SUMMARY_METHOD_FAX:
						if( !$payment_type->summary_fax ){
							$summary_error = 'Summary fax missing';
						}
				case 'no summary':
						$summary_error = false;
					break;
			}

			$restaurant = $_restaurant->payment_data;
			$lastPayment = $_restaurant->getLastPayment();
			if( $lastPayment->id_payment ){
				$_lastPayment = [];
				$_lastPayment[ 'amount' ] = $lastPayment->amount;
				$_lastPayment[ 'date' ] = $lastPayment->date()->format( 'M jS Y g:i:s A' );
				$_lastPayment[ 'id_payment' ] = $lastPayment->id_payment;
				$restaurant[ 'last_payment' ] = $_lastPayment;
			}
			$restaurant[ 'summary_method' ] = $payment_type->summary_method;
			$restaurant[ 'summary_error' ] = $summary_error;
			$restaurant[ 'name' ] = $_restaurant->name;
			$restaurant[ 'permalink' ] = $_restaurant->permalink;
			$restaurant[ 'has_payment_type' ] = $_restaurant->hasPaymentType();
			$restaurant[ 'delivery_service' ] = $_restaurant->delivery_service;
			$restaurant[ 'formal_relationship' ] = intval( $_restaurant->formal_relationship > 0 ) ? true : false;

			$payment_type = $_restaurant->paymentType();

			$restaurant[ 'payment_method' ] = false;

			switch ( $payment_type->payment_method ) {
				case Crunchbutton_Restaurant_Payment_Type::PAYMENT_METHOD_DEPOSIT:
					$restaurant[ 'payment_method' ] = Crunchbutton_Restaurant_Payment_Type::PAYMENT_METHOD_DEPOSIT;
					break;

				case Crunchbutton_Restaurant_Payment_Type::PAYMENT_METHOD_CHECK:
					$restaurant[ 'payment_method' ] = Crunchbutton_Restaurant_Payment_Type::PAYMENT_METHOD_CHECK;
					break;

				case Crunchbutton_Restaurant_Payment_Type::PAYMENT_METHOD_NO_PAYMENT:
					$restaurant[ 'payment_method' ] = Crunchbutton_Restaurant_Payment_Type::PAYMENT_METHOD_NO_PAYMENT;
					break;
			}

			$restaurant[ 'has_payment_type' ] = $_restaurant->hasPaymentType();

			$restaurant[ 'id_restaurant' ] = $_restaurant->id_restaurant;
			$restaurant[ 'not_included' ] = 0;
			$restaurant[ 'orders_count' ] = 0;
			$restaurant[ 'refunded_count' ] = 0;
			$restaurant[ 'reimburse_cash_orders' ] = 0;

			if( $id_restaurant && $id_restaurant == $restaurant[ 'id_restaurant' ] ){
				$restaurant[ 'show_orders' ] = true;
			}

			$orders = [];

			foreach ( $_restaurant->_payableOrders as $_order ) {

				$alreadyPaid = Cockpit_Payment_Schedule_Order::checkOrderWasPaidRestaurant( $_order->id_order );
				if( !$alreadyPaid ){
					$alreadyPaid = Crunchbutton_Order_Transaction::checkOrderWasPaidRestaurant( $_order->id_order );
				}

				if( !$alreadyPaid ){
					$order = [];
					$order[ 'id_order' ] = $_order->id_order;
					$order[ 'name' ] = $_order->name;
					$order[ 'refunded' ] = ( $_order->refunded ) ? true : false;
					$order[ 'do_not_pay_restaurant' ] = ( $_order->do_not_pay_restaurant ) ? true : false;
					$order[ 'pay_if_refunded' ] = ( $_order->pay_if_refunded ) ? true : false;
					$order[ 'reimburse_cash_order' ] = ( $_order->reimburse_cash_order ) ? true : false;
					$order[ 'pay_type' ] = ucfirst( $_order->pay_type );
					if( $_order->do_not_pay_restaurant ){
						$order[ 'included' ] = false;
					} else {
						$order[ 'included' ] = ( !$_order->refunded ) ? true : ( $_order->refunded && $_order->pay_if_refunded ) ? true : false;
					}
					if( $_order->refunded ){
						$restaurant[ 'refunded_count' ]++;
					}

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
			}

			$restaurant[ 'pay' ] = true;
			$restaurant[ 'adjustment' ] = 0;
			$restaurant[ 'orders' ] = $orders;
			$restaurant[ 'total_due_without_adjustment' ] = $restaurant[ 'total_due' ];
			if( floatval( $restaurant[ 'orders_count' ] ) > 0 ){
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
		if( $order->pay_if_refunded ){
			$order->do_not_pay_restaurant = 0;
		}
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

	private function _restaurantDoNotPayForOrder(){
		$id_order = $this->request()['id_order'];
		$do_not_pay_restaurant = $this->request()['do_not_pay_restaurant'];
		$order = Order::o( $id_order );
		$order->do_not_pay_restaurant = ( intval( $do_not_pay_restaurant ) > 0 ) ? 1 : 0;
		$order->save();
		echo json_encode( [ 'id_order' => $order->id_order, 'id_restaurant' => $order->id_restaurant ] );
	}

	private function _restaurantSchedule(){
		if( Settlement::canCreateSettlementQueue( Crunchbutton_Queue::TYPE_SETTLEMENT_RESTAURANT ) ){
			$q = Queue::create([
				'type' 			=> Crunchbutton_Queue::TYPE_SETTLEMENT_RESTAURANT,
				'id_admin' 	=> c::user()->id_admin,
				'info'			=> file_get_contents('php://input')
			]);
			echo json_encode( [ 'success' => true ] );
			exit;
		} else {
			echo json_encode( [ 'error' => true ] );
		}
	}

	private function _restaurantPayments(){

		$resultsPerPage = $this->resultsPerPage;

		$page = max( $this->request()['page'], 1 );
		$id_restaurant = max( $this->request()['id_restaurant'], 0 );
		$start = ( ( $page - 1 ) * $resultsPerPage );

		$payments = Crunchbutton_Payment::listPayments( [ 'limit' => $start . ',' . $resultsPerPage, 'id_restaurant' => $id_restaurant, 'type' => 'restaurant' ] );
		$payments_total = Crunchbutton_Payment::listPayments( [ 'id_restaurant' => $id_restaurant, 'type' => 'restaurant' ] );
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

		if( $payments_total > 0 ){
			$pages = ceil( $payments_total / $resultsPerPage );
		}

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
		$id_payment = c::getPagePiece( 4 );
		$settlement = new Settlement;
		if( $settlement->sendRestaurantPaymentNotification( $id_payment ) ){
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
			$out = [ 'restaurants' => '', 'scheduled' => 0, 'processing' => 0, 'done' => 0, 'error' => 0, 'total' => 0 ];
			foreach( $schedules as $_schedule ){
				$data = $_schedule->exports();
				if( !$data[ 'amount' ] ){
					$data[ 'amount' ] = 0;
				}
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

	private function _restaurantScheduleArbitraryPayment(){

		$id_restaurant = $this->request()['id_restaurant'];
		$amount = $this->request()['amount'];
		$pay_type = $this->request()['pay_type'];
		$notes = $this->request()['notes'];

		$settlement = new Settlement;
		$id_payment_schedule = $settlement->scheduleRestaurantArbitraryPayment( $id_restaurant, $amount, $pay_type, $notes );
		if( $id_payment_schedule ){
			echo json_encode( [ 'success' => $id_payment_schedule ] );exit();
		} else {
			echo json_encode( [ 'error' => true ] );exit();
		}
	}

	public function _restaurantDownloadSummary(){
		$id_payment =  c::getPagePiece( 4 );
		$settlement = new Crunchbutton_Settlement;
		$summary = $settlement->restaurantSummaryByIdPayment( $id_payment );
		$filename = $summary[ 'restaurant' ] . ' - Payment ' . $id_payment . '.html';
		$mail = new Crunchbutton_Email_Payment_Summary( [ 'summary' => $summary ] );
		$summary = $mail->message();
		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		header( 'Content-Length: ' . mb_strlen( $summary, '8bit' ) );
		echo $summary;
	}

	public function _restaurantViewSummary(){
		$id_payment =  c::getPagePiece( 4 );
		$settlement = new Crunchbutton_Settlement;
		$summary = $settlement->restaurantSummaryByIdPayment( $id_payment );
		$mail = new Crunchbutton_Email_Payment_Summary( [ 'summary' => $summary ] );
		header( 'Content-Type: text/html' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		echo $mail->message();
	}

	private function _restaurantCheckPaymentStatus(){
		$id_payment = $this->request()['id_payment'];
		$payment = Crunchbutton_Payment::o( $id_payment );
		if( $payment->id_payment ){
			$status = $payment->checkPaymentStatus();
			echo json_encode( [ 'success' => $status ] );
		} else {
			echo json_encode( [ 'error' => 'Payment not found!' ] );
		}
	}

	private function _driverCheckPaymentStatus(){
		$id_payment = $this->request()['id_payment'];
		$payment = Crunchbutton_Payment::o( $id_payment );
		if( $payment->id_payment ){
			$status = $payment->checkPaymentStatus();
			echo json_encode( [ 'success' => $status ] );
		} else {
			echo json_encode( [ 'error' => 'Payment not found!' ] );
		}
	}

	private function _driverOldPayments(){
		$id_driver = $this->request()['id_driver'];
		if( !$id_driver ){
			$this->_error();
		}

		$settlement = new Settlement;

		$orders = $settlement->paidOrdersByDriverBeforeSettlement( $id_driver );

		$out = [];

		foreach ( $orders as $key => $val ) {

			if( !$orders[ $key ][ 'name' ] ){
				continue;
			}

			$driver = $orders[ $key ];

			$driver[ 'orders' ] = [];
			$driver[ 'not_included' ] = 0;
			if( $orders[ $key ][ 'orders' ] ){
				foreach( $orders[ $key ][ 'orders' ] as $order ){
					$_order = [];
					$_order[ 'id_order' ] = $order[ 'id_order' ];
					$_order[ 'name' ] = $order[ 'name' ];
					$_order[ 'restaurant' ] = $order[ 'restaurant' ];
					$_order[ 'pay_type' ] = ucfirst( $order[ 'pay_type' ] );
					$_order[ 'total' ] = $order[ 'final_price_plus_delivery_markup' ];
					$_order[ 'tip' ] = $order[ 'pay_info' ][ 'tip' ] ;
					$_order[ 'delivery_fee' ] = $order[ 'pay_info' ][ 'delivery_fee' ] ;
					$_order[ 'standard_reimburse' ] = $order[ 'pay_info' ][ 'standard_reimburse' ] ;
					$_order[ 'total_reimburse' ] = $order[ 'pay_info' ][ 'total_reimburse' ] ;
					$_order[ 'total_payment' ] = $order[ 'pay_info' ][ 'total_payment' ] ;
					$_order[ 'date' ] = $order[ 'date' ];
					$_order[ 'refunded' ] = $order[ 'refunded' ];
					$_order[ 'included' ] = !$order[ 'do_not_pay_driver' ];
					if( !$_order[ 'included' ] ){
						$driver[ 'not_included' ]++;
					}
					$driver[ 'orders' ][] = $_order;
					$total_orders++;
				}
			}
			$driver[ 'total_payment_without_adjustment' ] = $driver[ 'total_payment' ];
			$driver[ 'adjustment' ] = 0;
			$driver[ 'adjustment_notes' ] = '';
			$driver[ 'standard_reimburse' ] = ( $driver[ 'standard_reimburse' ] ? $driver[ 'standard_reimburse' ] : 0 );
			$driver[ 'total_reimburse' ] = ( $driver[ 'total_reimburse' ] ? $driver[ 'total_reimburse' ] : 0 );
			$driver[ 'total_payment' ] = ( $driver[ 'total_payment' ] ? $driver[ 'total_payment' ] : 0 );
			$driver[ 'pay' ] = true;

			$driver[ 'orders_count' ] = count( $driver[ 'orders' ] );

			if( !$driver[ 'pay_type' ] || !$driver[ 'pay_type' ][ 'payment_type' ] ){
				$driver[ 'pay_type' ] = [ 'payment_type' => '-' ];
			}

			if( $id_driver ){
				if( $id_driver == $driver[ 'id_admin' ] ){
					$out[ 'driver' ] = $driver;
				}
			} else {
				$out[ 'driver' ] = $driver;
			}
		}
		echo json_encode( $out );exit;
	}

	private function _driverBegin(){

		$start = $this->request()['start'];
		$end = $this->request()['end'];
		$id_driver = $this->request()['id_driver'];

		if( !$start || !$end ){
			$this->_error();
		}

		$settlement = new Settlement( [ 'start' => $start, 'end' => $end ] );

		$orders = $settlement->startDriver();
		$out = [ 	'drivers' => [],
							'notes' => Crunchbutton_Settlement::DEFAULT_NOTES,
							'payment' => Cockpit_Payment_Schedule::PAY_TYPE_PAYMENT,
							'reimbursement' => Cockpit_Payment_Schedule::PAY_TYPE_REIMBURSEMENT ];
		foreach ( $orders as $key => $val ) {
			if( !$orders[ $key ][ 'name' ] ){
				continue;
			}
			$driver = $orders[ $key ];
			if( $id_driver && $id_driver == $driver[ 'id_admin' ] ){
				$driver[ 'show_orders' ] = true;
			}
			$total_drivers++;
			$driver[ 'orders' ] = [];
			$driver[ 'not_included' ] = 0;
			if( $orders[ $key ][ 'orders' ] ){
				foreach( $orders[ $key ][ 'orders' ] as $order ){
					$_order = [];
					$_order[ 'id_order' ] = $order[ 'id_order' ];
					$_order[ 'name' ] = $order[ 'name' ];
					$_order[ 'restaurant' ] = $order[ 'restaurant' ];
					$_order[ 'pay_type' ] = ucfirst( $order[ 'pay_type' ] );
					$_order[ 'total' ] = $order[ 'final_price_plus_delivery_markup' ];
					$_order[ 'tip' ] = $order[ 'pay_info' ][ 'tip' ] ;
					$_order[ 'customer_fee' ] = $order[ 'pay_info' ][ 'customer_fee' ] ;
					$_order[ 'customer_fee_collected' ] = $order[ 'pay_info' ][ 'customer_fee_collected' ] ;
					$_order[ 'standard_reimburse' ] = $order[ 'pay_info' ][ 'standard_reimburse' ] ;
					$_order[ 'force_to_be_commissioned' ] = $order[ 'pay_info' ][ 'force_to_be_commissioned' ] ;
					$_order[ 'markup' ] = $order[ 'pay_info' ][ 'markup' ] ;
					$_order[ 'amount_per_order' ] = $order[ 'pay_info' ][ 'amount_per_order' ] ;
					$_order[ 'delivery_fee' ] = $order[ 'pay_info' ][ 'delivery_fee' ] ;
					$_order[ 'delivery_fee_collected' ] = $order[ 'pay_info' ][ 'delivery_fee_collected' ] ;
					$_order[ 'total_reimburse' ] = $order[ 'pay_info' ][ 'total_reimburse' ] ;
					$_order[ 'total_payment' ] = $order[ 'pay_info' ][ 'total_payment' ] ;
					$_order[ 'date' ] = $order[ 'date' ];
					$_order[ 'refunded' ] = $order[ 'refunded' ];
					$_order[ 'included' ] = !$order[ 'do_not_pay_driver' ];
					if( !$_order[ 'included' ] ){
						$driver[ 'not_included' ]++;
					}
					$driver[ 'orders' ][] = $_order;
					$total_orders++;
				}
			}
			$driver[ 'total_payment_without_adjustment' ] = $driver[ 'total_payment' ];
			$driver[ 'adjustment' ] = 0;
			$driver[ 'adjustment_notes' ] = '';
			$driver[ 'standard_reimburse' ] = ( $driver[ 'standard_reimburse' ] ? $driver[ 'standard_reimburse' ] : 0 );
			$driver[ 'total_service_fee' ] = ( $driver[ 'service_fee' ] ? $driver[ 'service_fee' ] : 0 );
			$driver[ 'total_reimburse' ] = ( $driver[ 'total_reimburse' ] ? $driver[ 'total_reimburse' ] : 0 );
			$driver[ 'total_payment' ] = ( $driver[ 'total_payment' ] ? $driver[ 'total_payment' ] : 0 );
			$driver[ 'total_payment_per_order' ] = ( $driver[ 'total_payment_per_order' ] ? $driver[ 'total_payment_per_order' ] : 0 );
			$driver[ 'delivery_fee_collected' ] = ( $driver[ 'delivery_fee_collected' ] ? $driver[ 'delivery_fee_collected' ] : 0 );
			$driver[ 'customer_fee_collected' ] = ( $driver[ 'customer_fee_collected' ] ? $driver[ 'customer_fee_collected' ] : 0 );

			// #5489
			$_driver = Admin::o( $driver[ 'id_admin' ] );
			if( $_driver->phone || $_driver->pass || $_driver->txt || $_driver->email ){
				$driver[ 'pay' ] = true;
			} else {
				$driver[ 'pay' ] = false;
			}

			$driver[ 'orders_count' ] = count( $driver[ 'orders' ] );

			if( !$driver[ 'pay_type' ] || !$driver[ 'pay_type' ][ 'payment_type' ] ){
				$driver[ 'pay_type' ] = [ 'payment_type' => '-' ];
			}

			if( !$id_driver || ( $id_driver && $id_driver == $driver[ 'id_admin' ] ) ){
				$out[ 'drivers' ][] = $driver;
			}
		}
		echo json_encode( $out );
	}

	private function _driverDoErrPayments(){
		$settlement = new Crunchbutton_Settlement;
		$settlement->doDriverErrPayments();
		echo json_encode( [ 'success' => true ] );
	}

	private function _driverDoNotPayForOrder(){
		$id_order = $this->request()['id_order'];
		$id_driver = $this->request()['id_driver'];
		$do_not_pay_driver = $this->request()['do_not_pay_driver'];
		$order = Order::o( $id_order );
		$order->do_not_pay_driver = ( intval( $do_not_pay_driver ) > 0 ) ? 1 : 0;
		$order->save();
		echo json_encode( [ 'id_order' => $order->id_order, 'id_driver' => $id_driver ] );
	}

	private function _driverTransferDeliveryDriver(){
		$id_order = $this->request()['id_order'];
		$id_driver = $this->request()['id_driver'];
		Crunchbutton_Order_Action::changeTransferDeliveryDriver( $id_order, $id_driver );
		echo json_encode( [ 'success' => true ] );
	}

	private function _driverScheduleArbitraryPayment(){
		$id_driver = $this->request()['id_driver'];
		$amount = $this->request()['amount'];
		$pay_type = $this->request()['pay_type'];
		$notes = $this->request()['notes'];

		$settlement = new Settlement;
		$id_payment_schedule = $settlement->scheduleDriverArbitraryPayment( $id_driver, $amount, $pay_type, $notes );
		if( $id_payment_schedule ){
			echo json_encode( [ 'success' => $id_payment_schedule ] );exit();
		} else {
			echo json_encode( [ 'error' => true ] );exit();
		}
	}

	private function _driverSchedule(){

		if( Settlement::canCreateSettlementQueue( Crunchbutton_Queue::TYPE_SETTLEMENT_DRIVER ) ){
			$q = Queue::create([
				'type' 			=> Crunchbutton_Queue::TYPE_SETTLEMENT_DRIVER,
				'id_admin' 	=> c::user()->id_admin,
				'info'			=> file_get_contents('php://input')
			]);
			echo json_encode( [ 'success' => true ] );
			exit;
		} else {
			echo json_encode( [ 'error' => true ] );
		}
	}

	private function _driverChangeStatus(){
		$status = trim( $this->request()[ 'status' ] );
		$id_payment_schedule = $this->request()[ 'id_payment_schedule' ];
		$schedule = Cockpit_Payment_Schedule::o( $id_payment_schedule );
		if( $schedule->id_payment_schedule ){
			switch ( $status ) {
				case Cockpit_Payment_Schedule::STATUS_ARCHIVED:
				case Cockpit_Payment_Schedule::STATUS_DELETED:
					$schedule->status = $status;
					$schedule->log = "Payment {$status}";
					$schedule->status_date = date( 'Y-m-d H:i:s' );
					if( $schedule->save() ){
						echo json_encode( [ 'success' => true ] );exit();
					}
				break;
			}
		}
		echo json_encode( [ 'error' => true ] );
	}

	private function _driverByStatus( $status ){
		$schedule = new Cockpit_Payment_Schedule;
		$schedules = $schedule->driverByStatus( $status );
		$out = [ 'drivers' => '', 'scheduled' => 0, 'processing' => 0, 'done' => 0, 'error' => 0, 'total_payments' => 0, 'total_reimbursements' => 0 ];
		foreach( $schedules as $_schedule ){
			$data = $_schedule->exports();
			if( !$data[ 'amount' ] ){
				$data[ 'amount' ] = 0;
			}
			$data[ 'date' ] = $_schedule->date()->format( 'M jS Y g:i:s A' );
			$out[ 'drivers' ][] = $data;
			$out[ $_schedule->status ]++;
			if( $_schedule->pay_type == Cockpit_Payment_Schedule::PAY_TYPE_REIMBURSEMENT ){
				$out[ 'total_reimbursements' ]++;
			} else {
				$out[ 'total_payments' ]++;
			}
		}
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$out[ 'updated_at' ] = $now->format( 'M jS Y g:i:s A' );
		echo json_encode( $out );
	}

	private function _driverDeleted(){
		$this->_driverByStatus( Cockpit_Payment_Schedule::STATUS_DELETED );
	}

	private function _driverArchived(){
		$this->_driverByStatus( Cockpit_Payment_Schedule::STATUS_ARCHIVED );
	}

	private function _driverScheduled(){
		if( c::getPagePiece( 4 ) ){
			$settlement = new Settlement;
			$id_payment_schedule = c::getPagePiece( 4 );
			$summary = $settlement->driverSummary( $id_payment_schedule );
			if( $summary ){
				echo json_encode( $summary );
			} else {
				$this->_error();
			}
		} else {

			$resultsPerPage = $this->resultsPerPage;

			$page = max( $this->request()['page'], 1 );
			$search = $this->request()['search'];
			$pay_type = max( $this->request()['type'], 0 );
			$status = max( $this->request()['status'], 0 );
			$start = ( ( $page - 1 ) * $resultsPerPage );

			$schedule = new Cockpit_Payment_Schedule;
			$schedules = $schedule->search( [ 'limit' => $start . ',' . $resultsPerPage, 'search' => $search, 'type' => 'driver', 'pay_type' => $pay_type, 'status' => $status ] );

			$payments_total = $schedule->search( [ 'total' => true, 'search' => $search, 'type' => 'driver', 'pay_type' => $pay_type, 'status' => $status ] );

			$results = [];
			foreach( $schedules as $_schedule ){
				$data = $_schedule->exports();
				if( !$data[ 'amount' ] ){
					$data[ 'amount' ] = 0;
				}
				$data[ 'date' ] = $_schedule->date()->format( 'M jS Y g:i:s A' );
				$results[] = $data;
			}

			if( $payments_total > 0 ){
				$pages = ceil( $payments_total / $resultsPerPage );
			}

			$data = [];
			$data[ 'count' ] = $payments_total;
			$data[ 'pages' ] = $pages;
			$data[ 'prev' ] = ( $page > 1 ) ? $page - 1 : null;
			$data[ 'page' ] = intval( $page );
			$data[ 'next' ] = ( $page < $pages ) ? $page + 1 : null;
			$data[ 'results' ] = $results;

			echo json_encode( $data );
		}
	}

	private function _driverPayments(){

		$resultsPerPage = $this->resultsPerPage;

		$page = max( $this->request()['page'], 1 );
		$search = $this->request()['search'];
		$id_driver = $this->request()['id_driver'];
		$pay_type = max( $this->request()['pay_type'], 0 );
		$payment_status = max( $this->request()['payment_status'], 0 );
		$start = ( ( $page - 1 ) * $resultsPerPage );

		$payments_total = Crunchbutton_Payment::listPayments( [ 'search' => $search, 'type' => 'driver', 'pay_type' => $pay_type, 'payment_status' => $payment_status, 'id_driver' => $id_driver ], true );
		$payments = Crunchbutton_Payment::listPayments( [ 'limit' => $start . ',' . $resultsPerPage, 'search' => $search, 'type' => 'driver', 'pay_type' => $pay_type, 'payment_status' => $payment_status, 'id_driver' => $id_driver ] );

		$list = [];
		foreach( $payments as $payment ){
			$data = $payment->exports();
			$data[ 'date' ] = $payment->date()->format( Settlement::date_format() );
			unset( $data[ 'id_restaurant' ] );
			unset( $data[ 'check_id' ] );
			unset( $data[ 'note' ] );
			unset( $data[ 'notes' ] );
			unset( $data[ 'type' ] );
			unset( $data[ 'id' ] );
			$data[ 'amount' ] = !$data[ 'amount' ] ? 0 : $data[ 'amount' ];
			$list[] = $data;
		}

		if( $payments_total > 0 ){
			$pages = ceil( $payments_total / $resultsPerPage );
		}

		$data = [];
		$data[ 'count' ] = $payments_total;
		$data[ 'pages' ] = $pages;
		$data[ 'prev' ] = ( $page > 1 ) ? $page - 1 : null;
		$data[ 'page' ] = intval( $page );
		$data[ 'next' ] = ( $page < $pages ) ? $page + 1 : null;
		$data[ 'results' ] = $list;

		echo json_encode( $data );
	}

	private function _driverPayment(){
		$settlement = new Settlement;
		$id_payment = c::getPagePiece( 4 );
		$summary = $settlement->driverSummaryByIdPayment( $id_payment );
		if( $summary ){
			echo json_encode( $summary );
		} else {
			$this->_error();
		}
	}

	private function _driverDoForcePayment(){
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
			$schedule->status = Cockpit_Payment_Schedule::STATUS_SCHEDULED;
			$schedule->log = 'Schedule created';
			$schedule->save();

			$settlement = new Settlement;
			$settlement->payDriver( $schedule->id_payment_schedule );

			echo json_encode( [ 'success' => true ] );
		} else {
			echo json_encode( [ 'error' => 'Payment schedule not found!' ] );
		}
	}

	private function _driverDoPayment(){
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
			$schedule->status = Cockpit_Payment_Schedule::STATUS_SCHEDULED;
			$schedule->log = 'Schedule created';
			$schedule->save();
			echo json_encode( [ 'success' => true ] );
		} else {
			echo json_encode( [ 'error' => 'Payment schedule not found!' ] );
		}
	}

	private function _driverSendSummary(){
		$id_payment = c::getPagePiece( 4 );
		$settlement = new Settlement;
		if( $settlement->sendDriverPaymentNotification( $id_payment ) ){
			echo json_encode( [ 'success' => true ] );
		} else {
			echo json_encode( [ 'error' => true ] );
		}
	}

	public function _infoPayRoll(){

		$year = c::getPagePiece( 4 ) ? c::getPagePiece( 4 ) : date( 'Y' ) ;
		$query = "SELECT
					  p.id_driver,
					  a.name,
					  a.phone,
					  a.email,
					  SUM( amount ) AS payment
					  FROM payment p
					  INNER JOIN admin a ON a.id_admin = p.id_driver
					  WHERE YEAR( p.date ) = ?
					  AND p.pay_type = 'payment'
					  AND p.stripe_id IS NOT NULL
					  AND p.env = 'live' AND p.payment_status = 'succeeded'
					GROUP BY id_driver
					ORDER BY a.name ASC
		";

		$payments = c::db()->get( $query, [ $year ] );
		$out = [];
		$out[] = join( ',', [	'ID', 'Name', 'Address', 'SSN','Amount' ] );
		foreach( $payments as $payment ){
			$driver = Admin::o( $payment->id_driver );
			$address = $driver->payment_type()->address;
			$address = str_replace( "\n", "", $address );
			$address = str_replace( ",", ";", $address );
			$name = $payment->name ? $payment->name : $driver->name;
			$ssn = $driver->ssn();
			$out[] = join( ',', [	$payment->id_driver,
														$name,
														$address,
														$ssn,
														$payment->payment
								] );
		}
		header('Expires: 0');
		header('Cache-control: private');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Description: File Transfer');
		header('Content-Type: text/csv');
		header('Content-disposition: attachment; filename="info-for-1099-year-' . $year . '.csv"');

		echo preg_replace(["/\r\n/","/\r/"],["\n","\n"], join( "\n", $out ) );
	}

	public function _driverDownloadSummary(){
		$id_payment =  c::getPagePiece( 4 );
		$settlement = new Crunchbutton_Settlement;
		$summary = $settlement->driverSummary( $id_payment );
		$filename = $summary[ 'driver' ] . ' - Payment ' . $id_payment . '.html';
		$mail = new Crunchbutton_Email_Payment_Summary( [ 'summary' => $summary ] );
		$summary = $mail->message();
		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		header( 'Content-Length: ' . mb_strlen( $summary, '8bit' ) );
		echo $summary;
	}

	public function _driverViewSummary(){
		$id_payment =  c::getPagePiece( 4 );
		$settlement = new Crunchbutton_Settlement;
		$summary = $settlement->driverSummary( $id_payment );
		$summary = $settlement->driverSummary( $id_payment );
		$mail = new Crunchbutton_Email_Payment_Summary( [ 'summary' => $summary ] );
		header( 'Content-Type: text/html' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		echo $mail->message();
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

	private function _queueList(){
		$limit = $this->request()['limit'] ? $this->request()['limit'] : $this->resultsPerPage;
		$page = $this->request()['page'] ? $this->request()['page'] : 1;
		$status = ( $this->request()['status'] ) ? $this->request()['status'] : null;
		$type = ( $this->request()['type'] ) ? $this->request()['type'] : null;


		$q = '
					SELECT -WILD- FROM queue q
					LEFT JOIN queue_type qt ON qt.id_queue_type = q.id_queue_type
					LEFT JOIN admin a ON a.id_admin = q.id_admin
				';

		$q .= ' WHERE 1 = 1 ';

		$keys = [];

		if( $type ){
			$q .= ' AND q.type = ? ';
			$keys[] = $type;
		} else {
			$q .= ' AND ( q.type = ? OR q.type = ? ) ';
			$keys[] = Crunchbutton_Queue::TYPE_SETTLEMENT_DRIVER;
			$keys[] = Crunchbutton_Queue::TYPE_SETTLEMENT_RESTAURANT;
		}

		if( $status ){
			$q .= ' AND q.status = ? ';
			$keys[] = $status;
		}

		if ($page == 1) {
			$offset = 0;
		} else {
			$offset = intval( ($page-1) * $limit );
		}

		$count = 0;

		$r = c::db()->query(str_replace( '-WILD-','COUNT(*) as c', $q), $keys);
		while ($c = $r->fetch()) {
			$count = intval( $c->c );
		}

		$q .= ' ORDER BY id_queue DESC ';

		$q .= ' LIMIT '.intval($limit).' OFFSET '.intval($offset).' ';

		$query = str_replace( '-WILD-',' q.id_queue, q.type, q.date_end AS finished_at, q.date_start AS started_at, q.status, qt.type, a.name, q.info ', $q );

		$r = c::db()->query($query, $keys);

		$i = 1;

		while ( $q = $r->fetch() ) {
			$info = json_decode( $q->info );

			$q->range = ( new DateTime( $info->start ) )->format( 'm/d/Y' );
			$q->range .= ' => ';
			$q->range .= ( new DateTime( $info->end ) )->format( 'm/d/Y' );

			$q->pay_type = $info->pay_type;

			unset( $q->info );

			$data[] = $q;
		}

		$pages = ceil($count / $limit);

		echo json_encode([
			'more' => ( $pages > $page ),
			'count' => intval( $count ),
			'pages' => $pages,
			'page' => intval($page),
			'results' => $data
		], JSON_PRETTY_PRINT);

	}

	private function _paymentList(){

		$limit = $this->request()['limit'] ? $this->request()['limit'] : $this->resultsPerPage;
		$limit = intval( $limit );
		$search = $this->request()['search'] ? $this->request()['search'] : null;
		$page = $this->request()['page'] ? $this->request()['page'] : 1;
		$id_driver = ( $this->request()['id_driver'] ) ? $this->request()['id_driver'] : null;
		$payment_type = ( $this->request()['payment_type'] && $this->request()['payment_type'] != '0' ) ? $this->request()['payment_type'] : null;
		$status = ( $this->request()['status'] && $this->request()['status'] != '0' ) ? $this->request()['status'] : null;
		$date = ( $this->request()['date'] && $this->request()['date'] != '0' ) ? $this->request()['date'] : null;
		$getCount = $this->request()['fullcount'] && $this->request()['fullcount'] != 'false' ? true : false;
		$page = max( $this->request()['page'], 1 );
		$search = $this->request()['search'];
		$type = $this->request()['type'];
		$date = $this->request()['date'];
		$payment_type = $this->request()['payment_type'];
		$status = $this->request()['status'];
		$start = ( ( $page - 1 ) * $resultsPerPage );

		if ($page == 1) {
			$offset = 0;
		} else {
			$offset = intval( ($page-1) * $limit );
		}

		$q = '
					SELECT -WILD- FROM payment_schedule ps
					LEFT JOIN payment p ON p.id_payment = ps.id_payment
				';
		$keys = [];

		switch ( $type ) {
			case 'restaurant':
				$q .= '
								INNER JOIN restaurant r ON r.id_restaurant = ps.id_restaurant
						';
				break;

			case 'driver':
				$q .= '
								INNER JOIN admin d ON d.id_admin = ps.id_driver
								INNER JOIN admin_payment_type apt ON apt.id_admin = d.id_admin
						';
				if( $id_driver ){
					$q .= ' AND d.id_admin = ?';
					$keys = [ $id_driver ];
				}
				break;

			default:
				$q .= '
								LEFT JOIN restaurant r ON r.id_restaurant = ps.id_restaurant
								LEFT JOIN admin d ON d.id_admin = ps.id_driver
						';
				break;
		}

		$q .= ' WHERE 1 = 1 ';


		if( $payment_type ){
			$q .= ' AND ps.pay_type = ? ';
			$keys[] = $payment_type;
		}

		if( $status ){
			$q .= ' AND ps.status = ? ';
			$keys[] = $status;
		}

		if ($date) {
			$date = explode( 'T', $date );
			$date = $date[ 0 ];
			$q .= '
				AND ps.date > ? AND ps.date <= ?
			';
			$keys[] = $date;
			$keys[] = $date . ' 23:59:59';
		}

		if( $search ){
			switch ( $type ) {
				case 'restaurant':
					$s = Crunchbutton_Query::search([
						'search' => stripslashes( $search ),
						'fields' => [ 'r.name' => 'like' ]
					]);
					break;
				case 'driver':
					$s = Crunchbutton_Query::search([
						'search' => stripslashes( $search ),
						'fields' => [ 'd.name' => 'like' ]
					]);
					break;
				default:
					$s = Crunchbutton_Query::search([
						'search' => stripslashes($search),
						'fields' => [ 'r.name' => 'like', 'd.name' => 'like' ]
					]);
					break;
			}
			$q .= $s[ 'query' ];
			$keys = array_merge( $keys, $s[ 'keys' ] );
		}

		$count = 0;

		$r = c::db()->query(str_replace( '-WILD-','COUNT(*) as c', $q), $keys);
		while ($c = $r->fetch()) {
			$count = intval( $c->c );
		}

		$q .= ' ORDER BY id_payment_schedule DESC ';

		$q .= ' LIMIT '.intval($limit).' OFFSET '.intval($offset).' ';

		// do the query
		$data = [];

		switch ( $type ) {
			case 'restaurant':
				$query = str_replace( '-WILD-',' ps.*, p.id_payment, p.check_id, p.payment_status, r.name AS restaurant ', $q );
				break;

			case 'driver':
				$query = str_replace( '-WILD-','  ps.*, p.id_payment, p.check_id, p.payment_status, d.name AS driver, apt.payment_type ', $q );
				break;

			default:
				$query = str_replace( '-WILD-','  ps.*, p.id_payment, p.check_id, p.payment_status, r.name AS restaurant, d.name AS driver ', $q );
				break;
		}

		$r = c::db()->query($query, $keys);

		$i = 1;

		while ( $p = $r->fetch() ) {
			if( $p->type == Cockpit_Payment_Schedule::TYPE_RESTAURANT ){
				$p->name = $p->restaurant;
			}
			if( $p->type == Cockpit_Payment_Schedule::TYPE_DRIVER ){
				$p->name = $p->driver;
			}

			$p->amount = floatval( $p->amount );

			if( $p->check_id ){
				$p->check = true;
			}

			$data[] = $p;
		}

		$pages = ceil($count / $limit);
		echo json_encode([
			'more' => ( $pages > $page ),
			'count' => intval( $count ),
			'pages' => $pages,
			'page' => intval($page),
			'results' => $data
		], JSON_PRETTY_PRINT);

	}

}
