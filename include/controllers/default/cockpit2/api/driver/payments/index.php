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
			default:
				$this->_error();
				break;
		}
	}

	private function _payment(){
		if( c::getPagePiece( 4 ) ){
			$settlement = new Settlement;
			$id_payment_schedule = c::getPagePiece( 4 );
			$summary = $settlement->driverSummary( $id_payment_schedule );
			if( $summary[ 'id_driver' ] == c::user()->id_admin || c::admin()->permission()->check( [ 'global', 'drivers-all' ] ) ){
				foreach( $summary[ 'calcs' ] as $key => $value ){
					$summary[ 'calcs' ][ $key ] = ( $value < 0 ? ( $value * -1 ) : $value );
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