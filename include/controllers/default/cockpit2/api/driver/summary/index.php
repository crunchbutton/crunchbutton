<?php

class Controller_api_driver_summary extends Crunchbutton_Controller_RestAccount {

	public function init() {
		$this->_schedules = [];
		switch ( c::getPagePiece( 3 ) ) {
			case 'status':
				$this->_driverStatus();
				break;

			default:
				$this->_driverSummary();
				break;
		}
	}

	private function _driverStatus(){

		$this->_error();
	}

	private function _driverSummary(){

		if( c::getPagePiece( 3 ) && c::admin()->permission()->check( [ 'global', 'drivers-all' ] ) ){
			$id_driver = c::getPagePiece( 3 );
		} else {
			$id_driver = c::user()->id_admin;
		}
		$this->_summary( $id_driver );
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

	private function _summary( $id_driver ){

		$settlement = new Settlement();
		$driver = Admin::o( $id_driver );
		$keys = [];

		$keys[] = $id_driver;

		$out = [];

		// $out = array_merge( $out, $this->_invites( $id_driver ) );

		$page = $this->request()[ 'page' ] ? $this->request()[ 'page' ] : 1;

		$limit = 20;

		$out[ 'page' ] = intval( $page );
		$out[ 'results' ] = [];

		if ( $page == 1 ) {
			$offset = '0';
		} else {
			$offset = ( $page - 1 ) * $limit;
		}

		$pay_type = $this->request()[ 'type' ];

		if( $pay_type && $pay_type != 'all' ){
			$where = ' AND pay_type = "' . $pay_type . '"';
			// $keys[] = $pay_type;
		}

		$query = 'SELECT COUNT( * ) AS total FROM payment_schedule WHERE id_driver = ? ' . $where;

		$result = c::db()->get( $query, [$id_driver]);
		$out[ 'count' ] = intval( $result->_items[ 0 ]->total );
		$out[ 'pages' ] = ceil( $out[ 'count' ] / $limit );
		$query = 'SELECT * FROM payment_schedule WHERE id_driver = ? ' . $where . ' ORDER BY id_payment_schedule DESC LIMIT '.intval($limit).' OFFSET '.intval($offset);
		$payments = Cockpit_Payment_Schedule::q( $query, $keys);
		$has_error = false;

		foreach( $payments as $payment ){
			$_payment = [];
			$_payment[ 'id_payment_schedule' ] = $payment->id_payment_schedule;
			$_payment = array_merge( $_payment, Cockpit_Payment_Schedule::statusToDriver( $payment, true ) );
			$_payment[ 'amount' ] = floatval( ( !$payment->amount ? 0 : $payment->amount ) );
			if( $payment->pay_type != Cockpit_Payment_Schedule::PAY_TYPE_REIMBURSEMENT ){
				$_payment[ 'earnings' ] = ( $_payment[ 'amount' ] + $_payment[ 'collected_in_cash' ] );
			} else {
				$_payment[ 'collected_in_cash' ] = 0;
				$_payment[ 'earnings' ] = 0;
			}

			$_payment[ 'type' ] = ( $payment->pay_type == Cockpit_Payment_Schedule::PAY_TYPE_REIMBURSEMENT ) ? 'Reimbursement' : 'Payment';
			if( $payment->arbritary ){
				$_payment[ 'range_date' ] = $payment->note;
			}
			if( $_payment[ 'status' ] == 'Error' ){
				$has_error = true;
			}

			$out[ 'results' ][] = $_payment;
		}
		$out[ 'has_error' ] = $has_error;

		echo json_encode( $out );exit();
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}
