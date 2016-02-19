<?php

class Controller_api_driver_payments extends Crunchbutton_Controller_RestAccount {

	public function init() {

		switch ( c::getPagePiece( 3 ) ) {
			case 'all':
				$this->_payments();
				break;
			case 'payment':
				$this->_payment();
				break;
			case 'pay-roll-info':
				$this->_payRollInfo();
				break;
			default:
				$this->_error();
				break;
		}
	}

	private function _payRollInfo(){

		$year = 2015;

		$query = "SELECT
					  SUM( amount ) AS payment
					  FROM payment p
					  WHERE YEAR( p.date ) = ?
					  AND p.pay_type = 'payment'
					  AND ( p.stripe_id IS NOT NULL OR p.balanced_id IS NOT NULL )
					  AND p.env = 'live' AND p.payment_status = 'succeeded'
						AND id_driver = ?";

		$payment = c::db()->get( $query, [ $year, c::user()->id_admin ] )->get( 0 );
		echo json_encode( [ 'payment' => number_format( $payment->payment, 2 ) ] );exit;
	}

	private function _payment(){
		if( c::getPagePiece( 4 ) ){
			$settlement = new Settlement;
			$id_payment_schedule = c::getPagePiece( 4 );
			$summary = $settlement->driverSummary( $id_payment_schedule );

			if( $summary[ 'id_driver' ] == c::user()->id_admin || c::admin()->permission()->check( [ 'global', 'drivers-all' ] ) ){

				$summary[ 'has_stuff_to_remove' ] = false;
				$summary[ 'show_money_make_week' ] = false;

				if( $summary[ 'calcs' ] ){
					foreach( $summary[ 'calcs' ] as $key => $value ){
						$summary[ 'calcs' ][ $key ] = ( $value < 0 ? ( $value * -1 ) : $value );
					}
					if( $summary[ 'calcs' ][ 'amount_per_order' ] ||
							$summary[ 'calcs' ][ 'total_commissioned' ] ||
							$summary[ 'calcs' ][ 'total_commissioned_tip' ] ||
							$summary[ 'calcs' ][ 'tip' ] ||
							$summary[ 'invites_amount' ] ){
						$summary[ 'show_money_make_week' ] = true;
					}
					if( $summary[ 'calcs' ][ 'delivery_fee_collected' ] ||
							$summary[ 'calcs' ][ 'markup' ] ||
							$summary[ 'calcs' ][ 'customer_fee_collected' ] ||
							$summary[ 'calcs' ][ 'adjustment' ] ){
						$summary[ 'has_stuff_to_remove' ] = true;
					}

					if( $summary[ 'driver_payment_type' ] == Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_MAKING_WHOLE ){
						$summary[ 'calcs' ][ 'delivery_fee_plus_tips' ] = $summary[ 'calcs' ][ 'tip' ] + $summary[ 'calcs' ][ 'amount_per_order' ];
						if( $summary[ 'shifts_hours_amount' ] > $summary[ 'calcs' ][ 'delivery_fee_plus_tips' ] ){
							$summary[ 'calcs' ][ 'make_whole_amount' ] = $summary[ 'shifts_hours_amount' ] - $summary[ 'calcs' ][ 'delivery_fee_plus_tips' ];
							// before 10/15 make whole was also paying tips
							if( c::env() == 'live' && $summary[ 'id_payment_schedule' ] <= 47946 ){
								$summary[ 'calcs' ][ 'make_whole_amount' ] += $summary[ 'calcs' ][ 'tip' ];
							}
						}
					}
				}

				echo json_encode( $summary );
			} else {
				$this->_error();
			}
		} else {
			$this->_error();
		}
	}

	private function _payments(){
		if( c::getPagePiece( 4 ) && c::admin()->permission()->check( [ 'global', 'drivers-all' ] ) ){
			$id_driver = c::getPagePiece( 4 );
		} else {
			$id_driver = c::user()->id_admin;
		}

		$schedules = Cockpit_Payment_Schedule::driverPaymentByIdAdmin( $id_driver );
		$settlement = new Crunchbutton_Settlement;
		$out = [ 'payments' => [] ];
		foreach( $schedules as $payment ){
			$_payment = [];
			$_payment[ 'id_payment_schedule' ] = $payment->id_payment_schedule;
			$_payment = array_merge( $_payment, Cockpit_Payment_Schedule::statusToDriver( $payment ) );
			$_payment[ 'amount' ] = max( $payment->amount, 0 );
			$_payment[ 'type' ] = ( $payment->pay_type == Cockpit_Payment_Schedule::PAY_TYPE_REIMBURSEMENT ) ? 'Reimbursement' : 'Payment';
			if( $payment->pay_type == Cockpit_Payment_Schedule::PAY_TYPE_PAYMENT ){
				$summary = $settlement->driverSummary( $payment->id_payment_schedule );
				$_payment[ 'total_received_cash' ] = max( 0, $summary[ '_total_received_cash_' ] );
				$_payment[ 'total_cash_orders' ] = max( 0, $summary[ '_total_cash_orders_' ] );
			}
			$out[ 'payments' ][] = $_payment;
		}
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		echo json_encode( $out );
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}