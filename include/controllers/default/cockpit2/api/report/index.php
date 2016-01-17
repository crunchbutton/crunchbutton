<?php

class Controller_Api_Report extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if( !c::admin()->permission()->check( [ 'global' ] ) ){
			$this->_error();
		}

		switch ( c::getPagePiece( 2 ) ) {
			case 'first-time-user-gift-codes-used-per-school-per-day':
				$this->_first_time_user_gift_codes_used_per_school_per_day();
				break;
			default:
				$this->_error();
				break;
		}
	}

	private function _first_time_user_gift_codes_used_per_school_per_day(){
		if( !c::admin()->permission()->check( ['global', 'report-all', 'support-all', 'support-crud' ] ) ){
			$this->error(404, true);
		}

		$start = $this->request()[ 'start' ];
		$end = $this->request()[ 'end' ];

		if( !$start || !$end ){
			$this->_error();
		}

		$start = explode( '/' , $start );
		$start = $start[ 2 ] . '-' . $start[ 0 ] . '-' . $start[ 1 ];

		$end = explode( '/' , $end );
		$end = $end[ 2 ] . '-' . $end[ 0 ] . '-' . $end[ 1 ];

		echo json_encode( Crunchbutton_Report_FirstTimeUserGiftCodesUsedPerSchoolPerDay::report( $start, $end ) );exit;;

	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}
