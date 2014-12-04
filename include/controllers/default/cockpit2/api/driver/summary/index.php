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
		$range = [ 'end' => null, 'start' => null ];

		$payment_type = $driver->payment_type();
		if( $payment_type->payment_type && $payment_type->payment_type == Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS ){
			$this->_summaryByHour( $range, $id_driver );
		} else {
			$this->_summaryByOrder( $range, $id_driver );
		}
	}

	private function _invites( $id_driver ){
		$settlement = new Settlement();
		$amount_per_invited_user = $settlement->amount_per_invited_user();
		$invites = $settlement->driverInvites( $id_driver );
		$out = [];
		if( $invites ){
			$out[ 'referral' ] = [];
			$out[ 'referral' ][ 'amount_per_user' ] = $amount_per_invited_user;
			foreach( $invites as $id_admin => $invites ){
				$out[ 'referral' ][ 'invites' ] = $invites;
				$out[ 'referral' ][ 'invites_total' ] = count( $invites );
				$out[ 'referral' ][ 'invites_total_payment' ] = ( $amount_per_invited_user * $out[ 'referral' ][ 'invites_total' ] );
			}
		}
		return $out;
	}

	private function _summaryByHour( $range, $id_driver ){

		$settlement = new Settlement( $range );
		$shifts = $settlement->driverWeeksSummaryShifts( $id_driver );
		$driver = Admin::o( $id_driver );
		$payment_type = $driver->payment_type();
		$hour_rate = floatval( $payment_type->hour_rate );

		$out = [];

		$out = array_merge( $out, $this->_invites( $id_driver ) );

		$out = array_merge( $out, $this->_lastShift( $id_driver ) );

		if( $shifts ){

			$out[ 'type' ] = 'hour';
			$out[ 'payments' ] = [];
			$out[ 'weeks' ] = [];

			$payments = Cockpit_Payment_Schedule::driverPaymentByIdAdmin( $id_driver );
			$has_error = false;
			foreach( $payments as $payment ){
				$_payment = [];
				$_payment[ 'id_payment_schedule' ] = $payment->id_payment_schedule;
				$_payment = array_merge( $_payment, Cockpit_Payment_Schedule::statusToDriver( $payment ) );
				$_payment[ 'amount' ] = ( !$payment->amount ? 0 : $payment->amount );
				$_payment[ 'type' ] = ( $payment->pay_type == Cockpit_Payment_Schedule::PAY_TYPE_REIMBURSEMENT ) ? 'Reimbursement' : 'Payment';
				if( $payment->arbritary ){
					$_payment[ 'range_date' ] = 's'. $payment->note;
				}
				if( $_payment[ 'status' ] == 'Error' ){
					$has_error = true;
				}
				$out[ 'payments' ][] = $_payment;
			}

			$out[ 'has_error' ] = $has_error;

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
						$period = 'From ' . $_day->format( 'm/d/Y' );
						$_day->modify( '+ 6 days' );
						$period .= ' to ' . $_day->format( 'm/d/Y' );
						$out[ 'weeks' ][ $yearweek ] = [ 'period' => $period, 'total_payment' => 0, 'total_reimburse' => 0 ];
						$out[ 'weeks' ][ $yearweek ][ 'payment_status' ] = [  'pending' => [ 'payment' => 0, 'reimburse' => 0 ],
																					'processing' => [ 'payment' => 0, 'reimburse' => 0 ],
																					'paid' => [ 'payment' => 0, 'reimburse' => 0 ] ];
					}
				}

				$_shift = [];
				$_shift[ 'id_community_shift' ] = $shift[ 'id_community_shift' ];
				$_shift[ 'id_admin_shift_assign' ] = $shift[ 'id_admin_shift_assign' ];
				$_shift[ 'date_start' ] = $shift[ 'date_start' ];
				$_shift[ 'date_end' ] = $shift[ 'date_end' ];
				$_shift[ 'hours' ] = $shift[ 'driver_paid' ];
				$_shift[ 'total_payment' ] = round( $shift[ 'driver_paid' ] * $hour_rate, 2 );

				$_shift[ 'payment' ] = $this->_statusByPaymentId( $shift[ 'paid_info' ][ 'id_payment' ] );

				if( !$out[ 'weeks' ][ $yearweek ][ 'days' ][ $day ][ 'shifts' ] ){
					$out[ 'weeks' ][ $yearweek ][ 'days' ][ $day ][ 'day' ] = $shift[ 'date_day' ];
					$out[ 'weeks' ][ $yearweek ][ 'days' ][ $day ][ 'date' ] = $day;
					$out[ 'weeks' ][ $yearweek ][ 'days' ][ $day ][ 'total_payment' ] = 0;
					$out[ 'weeks' ][ $yearweek ][ 'days' ][ $day ][ 'shifts' ] = [];
				}
				$out[ 'weeks' ][ $yearweek ][ 'yearweek' ] = $yearweek;
				$out[ 'weeks' ][ $yearweek ][ 'days' ][ $day ][ 'shifts' ][] = $_shift;
				$out[ 'weeks' ][ $yearweek ][ 'days' ][ $day ][ 'total_payment' ] += $_shift[ 'total_payment' ];
				$out[ 'weeks' ][ $yearweek ][ 'days' ][ $day ][ 'total_reimburse' ] = 0;
				$out[ 'weeks' ][ $yearweek ][ 'total_payment' ] += $_shift[ 'total_payment' ];
				$out[ 'weeks' ][ $yearweek ][ 'payment_status' ][ strtolower( $_shift[ 'payment' ][ 'status' ] ) ][ 'payment' ] += $_shift[ 'total_payment' ];
			}

			// Merge the reimbursed and tipped orders
			$orders = $this->_summaryByOrder( $range, $id_driver, false );
			$reimbursed_tipped = [];
			if( $orders[ 'weeks' ] ){

				foreach( $orders[ 'weeks' ] as $orderkey => $orderval ){
					$yearweek = $orderval[ 'yearweek' ];
					$reimbursed_tipped[ $yearweek ] = [];
					foreach( $orderval[ 'days' ] as $daykey => $dayvalue ){
						if( ( floatval( $dayvalue[ 'total_reimburse' ] ) != 0 ) || floatval( $dayvalue[ 'total_payment' ] ) != 0 ){
							$date = $dayvalue[ 'date' ];
							$reimbursed_tipped[ $yearweek ][ $date ] = [];
							$reimbursed_tipped[ $yearweek ][ $date ][ 'total_reimburse' ] = $dayvalue[ 'total_reimburse' ];
							$reimbursed_tipped[ $yearweek ][ $date ][ 'total_payment' ] = $dayvalue[ 'total_payment' ];
							$reimbursed_tipped[ $yearweek ][ $date ][ 'orders' ] = [];
							foreach( $dayvalue[ 'orders' ] as $order ){
								$order[ 'delivery_fee' ] = 0;
								if( ( floatval( $order[ 'total_reimburse' ] ) != 0 ) || ( floatval( $order[ 'total_payment' ] ) != 0 ) ){
									$reimbursed_tipped[ $yearweek ][ $date ][ 'orders' ][] = $order;
								}
							}
						}
					}
				}
			}
			foreach( $out[ 'weeks' ] as $weekkey => $weekval ){
				$yearweek = $weekval[ 'yearweek' ];
				// it has reimbursed or tipped orders
				if( $reimbursed_tipped[ $yearweek ] ){
					$out[ 'weeks' ][ $yearweek ][ 'total_payment' ] = 0;
					foreach( $weekval[ 'days' ] as $daykey => $dayval ){
						if( $reimbursed_tipped[ $yearweek ][ $daykey ] ){
							$out[ 'weeks' ][ $weekkey ][ 'days' ][ $daykey ][ 'total_payment' ] += $reimbursed_tipped[ $yearweek ][ $daykey ][ 'total_payment' ];
							$out[ 'weeks' ][ $weekkey ][ 'days' ][ $daykey ][ 'total_reimburse' ] += $reimbursed_tipped[ $yearweek ][ $daykey ][ 'total_reimburse' ];
							foreach( $reimbursed_tipped[ $yearweek ][ $daykey ][ 'orders' ] as $order ){
								if( !$out[ 'weeks' ][ $weekkey ][ 'days' ][ $daykey ][ 'orders' ] ){
									$out[ 'weeks' ][ $weekkey ][ 'days' ][ $daykey ][ 'orders' ] = [];
								}
								$out[ 'weeks' ][ $weekkey ][ 'days' ][ $daykey ][ 'orders' ][] = $order;
								$out[ 'weeks' ][ $yearweek ][ 'payment_status' ][ strtolower( $order[ 'payment' ][ 'status' ] ) ][ 'payment' ] += $order[ 'total_payment' ];
								$out[ 'weeks' ][ $yearweek ][ 'payment_status' ][ strtolower( $order[ 'reimburse' ][ 'status' ] ) ][ 'reimburse' ] += $order[ 'total_reimburse' ];
							}
						}
						$out[ 'weeks' ][ $yearweek ][ 'total_payment' ] += $out[ 'weeks' ][ $weekkey ][ 'days' ][ $daykey ][ 'total_payment' ];
						$out[ 'weeks' ][ $yearweek ][ 'total_reimburse' ] += $out[ 'weeks' ][ $weekkey ][ 'days' ][ $daykey ][ 'total_reimburse' ];
					}
				}
			}

			usort( $out[ 'weeks' ], function( $a, $b ) {
				return intval( $a[ 'yearweek' ] ) < intval( $b[ 'yearweek' ] );
			} );

			foreach( $out[ 'weeks' ] as $weekkey => $weekval ){
				usort( $out[ 'weeks' ][ $weekkey ][ 'days' ], function( $a, $b ) {
					return intval( $a[ 'date' ] ) < intval( $b[ 'date' ] );
				} );
			}

			foreach( $out[ 'weeks' ] as $weekkey => $weekval ){
				if( isset( $out[ 'earnings' ][ 'current' ] ) ){
					$out[ 'earnings' ][ 'previous' ] = [ 'total_payment' => $weekval[ 'total_payment' ], 'total_reimburse' => $weekval[ 'total_reimburse' ], 'payment_status' => $weekval[ 'payment_status' ] ];
					break;
				}

				if( !isset( $out[ 'earnings' ][ 'current' ] ) ){
					$out[ 'earnings' ][ 'current' ] = [ 'total_payment' => $weekval[ 'total_payment' ], 'total_reimburse' => $weekval[ 'total_reimburse' ], 'payment_status' => $weekval[ 'payment_status' ] ];
				}
			}
			echo json_encode( $out );exit();

		} else {
			$out = [ 'weeks' => 0 ];
		}

		// Hide View Details section: #3727
		$out[ 'weeks' ] = 0;
		// Get rid of the "Earned Since..." section entirely. #3727
		$out[ 'earnings' ] = 0;

		echo json_encode( $out );exit();

	}

	private function _summaryByOrder( $range, $id_driver, $json = true ){

		/**
		  * @hideme:
		  * Hide View Details section: #3727
		  * Get rid of the "Earned Since..." section entirely. #3727
		 */

		// $orders = $settlement->driverWeeksSummaryOrders( $id_driver ); // @hideme
		// $settlement = new Settlement($range); // @hideme
		$out = [];
		$out['weeks'] = 0;
		$out['earnings'] = 0;
		$settlement = new Crunchbutton_Settlement;

		$out = array_merge( $out, $this->_invites( $id_driver ) );
		// $out = array_merge( $out, $this->_lastShift( $id_driver ) );  // @hideme

		// payments
		$out[ 'payments' ] = [];
		$payments = Cockpit_Payment_Schedule::driverPaymentByIdAdmin( $id_driver );
		$has_error = false;
		foreach( $payments as $payment ){
			$_payment = [];
			$_payment[ 'id_payment_schedule' ] = $payment->id_payment_schedule;
			$_payment[ 'total_orders' ] = $payment->total_orders();
			$_payment = array_merge( $_payment, Cockpit_Payment_Schedule::statusToDriver( $payment ) );
			$_payment[ 'amount' ] = ( !$payment->amount ? 0 : $payment->amount );
			$_payment[ 'type' ] = ( $payment->pay_type == Cockpit_Payment_Schedule::PAY_TYPE_REIMBURSEMENT ) ? 'Reimbursement' : 'Payment';
			if( $payment->pay_type == Cockpit_Payment_Schedule::PAY_TYPE_PAYMENT ){
				$summary = $settlement->driverSummary( $payment->id_payment_schedule );
				$_payment[ 'total_received_cash' ] = max( 0, $summary[ '_total_received_cash_' ] );
				$_payment[ 'total_cash_orders' ] = max( 0, $summary[ '_total_cash_orders_' ] );
			}
			if( $_payment[ 'status' ] == 'Error' ){
				$has_error = true;
			}
			if( $payment->arbritary ){
				$_payment[ 'range_date' ] = $payment->note;
			}
			$out[ 'payments' ][] = $_payment;
		}

		$out[ 'has_error' ] = $has_error;

		// weeks
		if( $orders[ 0 ] ){

			$orders = $orders[ 0 ];

			$out[ 'type' ] = 'order';
			$out[ 'weeks' ] = [];

			// orders
			if( $orders[ 'orders' ] && count( $orders[ 'orders' ] ) > 0 ){

				foreach( $orders[ 'orders' ] as $order ){
					$week = $order[ 'week' ];
					$year = $order[ 'year' ];
					$day = $order[ 'day' ];

					$yearweek = $year . $week;
					if( !$out[ 'weeks' ][ $week ] ){
						$week = $order[ 'week' ];
						if( !$out[ 'weeks' ][ $yearweek ] ){
							$_day = new DateTime( date( 'Y-m-d', strtotime(  $year . 'W' .  $week . '0' ) ), new DateTimeZone( c::config()->timezone ) );
							$period = 'From ' . $_day->format( 'm/d/Y' );
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
					$_order[ 'total_payment' ] = max( $order[ 'pay_info' ][ 'total_payment' ], 0 );

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
		}


		if( $json ){
			echo json_encode($out);
			exit;
		} else {
			return $out;
		}

	}

	private function _lastShift( $id_admin ){
		$shift = Crunchbutton_Community_Shift::getLastWorkedShiftByAdmin( $id_admin );
		$orders = $shift->deliveredOrdersByAdminAtTheShift( $id_admin );
		$orders = $orders->get(0);
		$_orders = [];
		$settlement = new Crunchbutton_Settlement;
		foreach ( $orders as $order ) {
			$_orders[] = $settlement->orderExtractVariables( $order );
		}
		$process = $settlement->driversProcess( $_orders );

		$last_shift = $process[ 0 ];

		return [ 'last_shift' => [ 'date' => $shift->dateStart()->get(0)->format( 'm/d/Y' ), 'total_payment' => $last_shift[ 'total_payment' ], 'total_reimburse' => $last_shift[ 'total_reimburse' ] ] ];
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