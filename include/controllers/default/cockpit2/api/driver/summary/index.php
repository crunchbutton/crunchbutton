<?php

class Controller_api_driver_summary extends Crunchbutton_Controller_RestAccount {

	public function init() {
		$this->_schedules = [];
		$this->_driverSummary();
	}

	private function _driverSummary(){

		if( c::getPagePiece( 3 ) && c::admin()->permission()->check( [ 'global', 'drivers-all' ] ) ){
			$id_driver = c::getPagePiece( 3 );
		} else {
			$id_driver = c::user()->id_admin;
		}

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$range = [ 'end' => $now->format( 'm/d/Y' ) ];
		$now->modify( '-2 week' );
		$range[ 'start' ] = $now->format( 'm/d/Y' );

		$settlement = new Settlement( $range );
		$driver = $settlement->driverWeeksSummaryOrders( $id_driver );

		if( $driver[ 0 ] ){

			$driver = $driver[ 0 ];

			$out = [ 'earnings' => [],  'payments' => [], 'weeks' => [] ];

			// payments
			$payments = Cockpit_Payment_Schedule::driverPaymentByIdAdmin( $id_driver );
			foreach( $payments as $payment ){
				$_payment = [];
				$_payment[ 'id_payment_schedule' ] = $payment->id_payment_schedule;
				$_payment = array_merge( $_payment, Cockpit_Payment_Schedule::statusToDriver( $payment ) );
				$_payment[ 'amount' ] = ( !$payment->amount ? 0 : $payment->amount );
				$_payment[ 'type' ] = ( $payment->pay_type == Cockpit_Payment_Schedule::PAY_TYPE_REIMBURSEMENT ) ? 'Reimbursement' : 'Payment';
				$out[ 'payments' ][] = $_payment;
			}

			// orders
			foreach( $driver[ 'orders' ] as $order ){
				$week = $order[ 'week' ];
				$year = $order[ 'year' ];
				$day = $order[ 'day' ];

				$yearweek = $year . $week;
				if( !$out[ 'weeks' ][ $week ] ){
					$week = $order[ 'week' ];
					if( !$out[ 'weeks' ][ $yearweek ] ){
						$_day = new DateTime( date( 'Y-m-d', strtotime(  $year . 'W' .  $week . '0' ) ), new DateTimeZone( c::config()->timezone ) );
						$period = 'Week from ' . $_day->format( 'm/d/Y' );
						$_day->modify( '+ 6 days' );
						$period .= ' to ' . $_day->format( 'm/d/Y' );
						$out[ 'weeks' ][ $yearweek ] = [ 'period' => $period, 'total_payment' => 0, 'total_reimburse' => 0 ];
						$out[ 'weeks' ][ $yearweek ][ 'payment_status' ] = [  'pending' => [ 'payment' => 0, 'reimburse' => 0 ],
																					'processing' => [ 'payment' => 0, 'reimburse' => 0 ],
																					'paid' => [ 'payment' => 0, 'reimburse' => 0 ] ];
					}
				}

				$_order = [];
				$_order[ 'id_order' ] = $order[ 'id_order' ];
				$_order[ 'name' ] = $order[ 'name' ];
				$_order[ 'tip' ] = $order[ 'pay_info' ][ 'tip' ];
				$_order[ 'pay_type' ] = ( $order[ 'credit' ] ? 'Card' : 'Cash' );
				$_order[ 'delivery_fee' ] = $order[ 'pay_info' ][ 'delivery_fee' ];
				$_order[ 'markup' ] = $order[ 'pay_info' ][ 'markup' ];
				$_order[ 'total_reimburse' ] = $order[ 'pay_info' ][ 'total_reimburse' ];
				$_order[ 'total_payment' ] = $order[ 'pay_info' ][ 'total_payment' ];

				$_order[ 'payment' ] = $this->statusByPaymentId( $order[ 'payment_info' ][ 'id_payment' ] );
				$_order[ 'reimburse' ] = $this->statusByPaymentId( $order[ 'reimbursed_info' ][ 'id_payment' ] );

				if( !$out[ 'weeks' ][ $yearweek ][ 'days' ][ $day ][ 'orders' ] ){
					$out[ 'weeks' ][ $yearweek ][ 'days' ][ $day ][ 'day' ] = $order[ 'date_day' ];
					$out[ 'weeks' ][ $yearweek ][ 'days' ][ $day ][ 'date' ] = $day;
					$out[ 'weeks' ][ $yearweek ][ 'days' ][ $day ][ 'total_reimburse' ] = 0;
					$out[ 'weeks' ][ $yearweek ][ 'days' ][ $day ][ 'total_payment' ] = 0;
					$out[ 'weeks' ][ $yearweek ][ 'days' ][ $day ][ 'orders' ] = [];
				}
				$out[ 'weeks' ][ $yearweek ][ 'yearweek' ] = $yearweek;
				$out[ 'weeks' ][ $yearweek ][ 'days' ][ $day ][ 'orders' ][] = $_order;
				$out[ 'weeks' ][ $yearweek ][ 'days' ][ $day ][ 'total_reimburse' ] += $_order[ 'total_reimburse' ];
				$out[ 'weeks' ][ $yearweek ][ 'days' ][ $day ][ 'total_payment' ] += $_order[ 'total_payment' ];
				$out[ 'weeks' ][ $yearweek ][ 'total_payment' ] += $_order[ 'total_payment' ];
				$out[ 'weeks' ][ $yearweek ][ 'total_reimburse' ] += $_order[ 'total_reimburse' ];

				$out[ 'weeks' ][ $yearweek ][ 'payment_status' ][ strtolower( $_order[ 'payment' ][ 'status' ] ) ][ 'payment' ] += $_order[ 'total_payment' ];
				$out[ 'weeks' ][ $yearweek ][ 'payment_status' ][ strtolower( $_order[ 'reimburse' ][ 'status' ] ) ][ 'reimburse' ] += $_order[ 'total_reimburse' ];
			}

			usort( $out[ 'weeks' ], function( $a, $b ) {
				return intval( $a[ 'yearweek' ] ) < intval( $b[ 'yearweek' ] );
			} );

			foreach( $out[ 'weeks' ] as $weekkey => $weekval ){
				if( isset( $out[ 'earnings' ][ 'current' ] ) ){
					$out[ 'earnings' ][ 'previous' ] = [ 'total_payment' => $weekval[ 'total_payment' ], 'total_reimburse' => $weekval[ 'total_reimburse' ], 'payment_status' => $weekval[ 'payment_status' ] ];
					break;
				}

				if( !isset( $out[ 'earnings' ][ 'current' ] ) ){
					$out[ 'earnings' ][ 'current' ] = [ 'total_payment' => $weekval[ 'total_payment' ], 'total_reimburse' => $weekval[ 'total_reimburse' ], 'payment_status' => $weekval[ 'payment_status' ] ];
				}
			}

			foreach( $out[ 'weeks' ] as $weekkey => $weekval ){

				usort( $out[ 'weeks' ][ $weekkey ][ 'days' ], function( $a, $b ) {
					return intval( $a[ 'date' ] ) < intval( $b[ 'date' ] );
				} );

				$out[ 'recent' ] = [];

				foreach( $out[ 'weeks' ][ $weekkey ][ 'days' ] as $daykey => $dayval ){
					$out[ 'recent' ] = $out[ 'weeks' ][ $weekkey ][ 'days' ][ $daykey ];
					$out[ 'recent' ][ 'yearweek' ] = $weekval[ 'yearweek' ];
					$out[ 'recent' ][ 'show' ] = true;
					break;
				}
				break;
			}

			echo json_encode( $out );exit();

		} else {
			$out = [ 'weeks' => 0 ];
		}

		echo json_encode( $out );exit();
	}

	private function scheduleByPaymentId( $id_payment ) {
		if( $id_payment ){
			if( !$this->_schedules[ $id_payment ] ){
				$this->_schedules[ $id_payment ] = [ 'schedule' => Cockpit_Payment_Schedule::q( 'SELECT * FROM payment_schedule WHERE id_payment = "' . $id_payment . '"' ) ];
			}
			return $this->_schedules[ $id_payment ][ 'schedule' ];
		}
		return false;
	}

	private function statusByPaymentId( $id_payment ){
		if( $id_payment ){
				if( !$this->_schedules[ $id_payment ] || !$this->_schedules[ $id_payment ][ 'status' ] ){
				$schedule = $this->scheduleByPaymentId( $id_payment );
				if( $schedule ){
					$this->_schedules[ $id_payment ][ 'status' ] = Cockpit_Payment_Schedule::statusToDriver( $schedule );
				} else {
					return false;
				}
			}
			return $this->_schedules[ $id_payment ][ 'status' ];
		}
		return [ 'status' => 'Pending' ];
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}