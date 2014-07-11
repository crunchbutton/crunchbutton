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

		$driver = Admin::o( $id_driver );

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$range = [ 'end' => $now->format( 'm/d/Y' ) ];
		$days = $now->format( 'N' ) + ( 7 * 12 ); // twelve weeks
		$now->modify( '-' . $days . ' days' );
		$range[ 'start' ] = $now->format( 'm/d/Y' );

		$payment_type = $driver->payment_type();
		if( $payment_type->payment_type && $payment_type->payment_type == Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS ){
			$this->_summaryByHour( $range, $id_driver );
		} else {
			$this->_summaryByOrder( $range, $id_driver );
		}
	}

	private function _summaryByHour( $range, $id_driver ){
		$settlement = new Settlement( $range );
		$shifts = $settlement->driverWeeksSummaryShifts( $id_driver );

		if( $shifts ){

			$out = [ 'type' => 'hour', 'payments' => [], 'weeks' => [] ];

			// // payments
			// $payments = Cockpit_Payment_Schedule::driverPaymentByIdAdmin( $id_driver );
			// foreach( $payments as $payment ){
			// 	$_payment = [];
			// 	$_payment[ 'id_payment_schedule' ] = $payment->id_payment_schedule;
			// 	$_payment = array_merge( $_payment, Cockpit_Payment_Schedule::statusToDriver( $payment ) );
			// 	$_payment[ 'amount' ] = ( !$payment->amount ? 0 : $payment->amount );
			// 	$_payment[ 'type' ] = ( $payment->pay_type == Cockpit_Payment_Schedule::PAY_TYPE_REIMBURSEMENT ) ? 'Reimbursement' : 'Payment';
			// 	$out[ 'payments' ][] = $_payment;
			// }

			// shifts
			foreach( $shifts as $shift ){

				$week = $shift[ 'week' ];
				$year = $shift[ 'year' ];
				$day = $shift[ 'day' ];

				$yearweek = $year . $week;
				if( !$out[ 'weeks' ][ $week ] ){
					$week = $shift[ 'week' ];
					if( !$out[ 'weeks' ][ $yearweek ] ){
						$_day = new DateTime( date( 'Y-m-d', strtotime(  $year . 'W' .  $week . '0' ) ), new DateTimeZone( c::config()->timezone ) );
						$period = $_day->format( 'm/d/Y' );
						$_day->modify( '+ 6 days' );
						$period .= ' to ' . $_day->format( 'm/d/Y' );
						$out[ 'weeks' ][ $yearweek ] = [ 'period' => $period, 'total_payment' => 0 ];
						$out[ 'weeks' ][ $yearweek ][ 'payment_status' ] = [  'pending' => [ 'payment' => 0 ],
																					'processing' => [ 'payment' => 0 ],
																					'paid' => [ 'payment' => 0 ] ];
					}
				}

				$_shift = [];
				$_shift[ 'id_community_shift' ] = $shift[ 'id_community_shift' ];
				$_shift[ 'id_admin_shift_assign' ] = $shift[ 'id_admin_shift_assign' ];
				$_shift[ 'date_start' ] = $shift[ 'date_start' ];
				$_shift[ 'date_end' ] = $shift[ 'date_end' ];
				$_shift[ 'total_payment' ] = $shift[ 'driver_paid' ];

				$_shift[ 'payment' ] = $this->_statusByPaymentId( $shift[ 'payment_info' ][ 'id_payment' ] );

				if( !$out[ 'weeks' ][ $yearweek ][ 'days' ][ $day ][ 'shifts' ] ){
					$out[ 'weeks' ][ $yearweek ][ 'days' ][ $day ][ 'day' ] = $shift[ 'date_day' ];
					$out[ 'weeks' ][ $yearweek ][ 'days' ][ $day ][ 'date' ] = $day;
					$out[ 'weeks' ][ $yearweek ][ 'days' ][ $day ][ 'total_payment' ] = 0;
					$out[ 'weeks' ][ $yearweek ][ 'days' ][ $day ][ 'shifts' ] = [];
				}
				$out[ 'weeks' ][ $yearweek ][ 'yearweek' ] = $yearweek;
				$out[ 'weeks' ][ $yearweek ][ 'days' ][ $day ][ 'shifts' ][] = $_shift;
				$out[ 'weeks' ][ $yearweek ][ 'days' ][ $day ][ 'total_payment' ] += $_shift[ 'total_payment' ];
				$out[ 'weeks' ][ $yearweek ][ 'total_payment' ] += $_shift[ 'total_payment' ];

				$out[ 'weeks' ][ $yearweek ][ 'payment_status' ][ strtolower( $_shift[ 'payment' ][ 'status' ] ) ][ 'payment' ] += $_shift[ 'total_payment' ];
			}

			usort( $out[ 'weeks' ], function( $a, $b ) {
				return intval( $a[ 'yearweek' ] ) < intval( $b[ 'yearweek' ] );
			} );

			foreach( $out[ 'weeks' ] as $weekkey => $weekval ){
				if( isset( $out[ 'earnings' ][ 'current' ] ) ){
					$out[ 'earnings' ][ 'previous' ] = [ 'total_payment' => $weekval[ 'total_payment' ], 'payment_status' => $weekval[ 'payment_status' ] ];
					break;
				}

				if( !isset( $out[ 'earnings' ][ 'current' ] ) ){
					$out[ 'earnings' ][ 'current' ] = [ 'total_payment' => $weekval[ 'total_payment' ], 'payment_status' => $weekval[ 'payment_status' ] ];
				}
			}

			foreach( $out[ 'weeks' ] as $weekkey => $weekval ){
				usort( $out[ 'weeks' ][ $weekkey ][ 'days' ], function( $a, $b ) {
					return intval( $a[ 'date' ] ) < intval( $b[ 'date' ] );
				} );
			}

			echo json_encode( $out );exit();

		} else {
			$out = [ 'weeks' => 0 ];
		}

		echo json_encode( $out );exit();

	}

	private function _summaryByOrder( $range, $id_driver ){

		$settlement = new Settlement( $range );
		$orders = $settlement->driverWeeksSummaryOrders( $id_driver );

		if( $orders[ 0 ] ){

			$orders = $orders[ 0 ];

			$out = [ 'type' => 'order', 'payments' => [], 'weeks' => [] ];

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
			foreach( $orders[ 'orders' ] as $order ){
				$week = $order[ 'week' ];
				$year = $order[ 'year' ];
				$day = $order[ 'day' ];

				$yearweek = $year . $week;
				if( !$out[ 'weeks' ][ $week ] ){
					$week = $order[ 'week' ];
					if( !$out[ 'weeks' ][ $yearweek ] ){
						$_day = new DateTime( date( 'Y-m-d', strtotime(  $year . 'W' .  $week . '0' ) ), new DateTimeZone( c::config()->timezone ) );
						$period = $_day->format( 'm/d/Y' );
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

				$_order[ 'payment' ] = $this->_statusByPaymentId( $order[ 'payment_info' ][ 'id_payment' ] );
				$_order[ 'reimburse' ] = $this->_statusByPaymentId( $order[ 'reimbursed_info' ][ 'id_payment' ] );

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

	private function _scheduleByPaymentId( $id_payment ) {
		if( $id_payment ){
			if( !$this->_schedules[ $id_payment ] ){
				$this->_schedules[ $id_payment ] = [ 'schedule' => Cockpit_Payment_Schedule::q( 'SELECT * FROM payment_schedule WHERE id_payment = "' . $id_payment . '"' ) ];
			}
			return $this->_schedules[ $id_payment ][ 'schedule' ];
		}
		return false;
	}

	private function _statusByPaymentId( $id_payment ){
		if( $id_payment ){
				if( !$this->_schedules[ $id_payment ] || !$this->_schedules[ $id_payment ][ 'status' ] ){
				$schedule = $this->_scheduleByPaymentId( $id_payment );
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