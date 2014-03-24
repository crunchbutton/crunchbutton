<?php

class Controller_drivers_shift extends Crunchbutton_Controller_Account {
	
	public function init() {

		c::view()->page = 'drivers';

		switch ( c::getPagePiece( 2 ) ) {
			
			case 'community':

				if ( c::admin()->permission()->check( [ 'global','drivers-all', 'drivers-working-hours' ] ) ) {

					switch ( c::getPagePiece( 3 ) ) {
						case 'add':
							$this->communityAdd();
							break;
						case 'edit':
							$this->communityEdit();
							break;
						default:
							$this->community();
							break;
					}
				}
				break;
			
			case 'schedule':
				switch ( c::getPagePiece( 3 ) ) {
					case 'driver':
						$this->scheduleDriver();
					break;
					default:
					break;
				}
				break;

			case 'status':
				switch ( c::getPagePiece( 3 ) ) {
					case 'shift':
						$this->statusShift();
					break;
					default:
					break;
				}
				break;

			case 'summary':

				if ( c::admin()->permission()->check( [ 'global','drivers-all', 'drivers-working-hours' ] ) ) {

					switch ( c::getPagePiece( 3 ) ) {
						case 'shift':
							$this->summaryShift();
						break;
						default:
							$this->summary();
						break;
					}
				}
				break;

			default:
				break;
		}
	}

	public function scheduleDriver(){

		$admin = Admin::o( c::admin()->id_admin );

		// Start week at monday #2666
		if( c::getPagePiece( 4 ) && c::getPagePiece( 5 ) ){
			$year = c::getPagePiece( 4 );
			$week = c::getPagePiece( 5 );
			$firstDay = new DateTime( date( 'Y-m-d', strtotime( $year . 'W' . $week . 1 ) ), new DateTimeZone( c::config()->timezone  ) );
		} else {
			$year = date( 'Y', strtotime( '- 1 day' ) );
			$week = date( 'W', strtotime( '- 1 day' ) );
			$firstDay = new DateTime( date( 'Y-m-d', strtotime( $year . 'W' . $week . 1 ) ), new DateTimeZone( c::config()->timezone  ) );
			if( date( 'l' ) == 'Monday' ){
				$firstDay->modify( '+ 2 week' );	
			} else {
				$firstDay->modify( '+ 1 week' );
			}
			
			$week = $firstDay->format( 'W' );
			$year = $firstDay->format( 'Y' );
		}


		$days = [];
		for( $i = 0; $i <= 6; $i++ ){
			$days[] = new DateTime( $firstDay->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
			$firstDay->modify( '+ 1 day' );
		}

		if( $week <= 1 ){
			$weekPrev = ( $year - 1 ) . '/52';
		} else {
			$weekPrev = ( $year ) . '/' . ( $week - 1 );
		}
		if( $week >= 52 ){
			$weekNext = ( $year + 1 ) . '/01';
		} else {
			$weekNext = ( $year ) . '/' . ( $week + 1 );
		}

		c::view()->weekPrev = $weekPrev;
		c::view()->weekNext = $weekNext;
		c::view()->week = $week;
		c::view()->year = $year;
		c::view()->status = Crunchbutton_Admin_Shift_Status::getByAdminWeekYear( $admin->id_admin, $week, $year );
		c::view()->days = $days;
		c::view()->from = new DateTime( $days[ 0 ]->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
		c::view()->to = new DateTime( $days[ 6 ]->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
		c::view()->communities = $admin->communitiesHeDeliveriesFor();
		c::view()->display( 'drivers/shift/schedule/driver' );
	}

	public function communityEdit(){
		$id_community_shift = c::getPagePiece( 4 );
		$shift = Crunchbutton_Community_Shift::o( $id_community_shift );
		if( $shift->id_community_shift ){
			c::view()->shift = $shift;
			c::view()->layout( 'layout/ajax' );
			c::view()->display( 'drivers/shift/community/edit' );
		}
	}

	public function communityAdd(){
		$id_community = c::getPagePiece( 4 );
		$year = c::getPagePiece( 5 ) ? c::getPagePiece( 5 ) : date( 'Y' );
		$month = c::getPagePiece( 6 ) ? c::getPagePiece( 6 ) : date( 'm' );
		$day = c::getPagePiece( 7 ) ? c::getPagePiece( 7 ) : date( 'd' );
		$week = c::getPagePiece( 8 ) ? c::getPagePiece( 8 ) : date( 'W' );

		// Start week at monday #2666
		$firstDay = new DateTime( date( 'Y-m-d', strtotime( $year . 'W' . $week . 1 ) ), new DateTimeZone( c::config()->timezone  ) );

		$days = [];
		for( $i = 0; $i <= 6; $i++ ){
			$days[] = new DateTime( $firstDay->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
			$firstDay->modify( '+ 1 day' );
		}

		if( $id_community ){
			c::view()->year = $year;
			c::view()->month = $month;
			c::view()->day = $day;
			c::view()->days = $days;
			c::view()->community = Crunchbutton_Community::o( $id_community );
			c::view()->layout( 'layout/ajax' );
			c::view()->display( 'drivers/shift/community/add' );
		}
	}

	public function summary(){

		// Start week at monday #2666
		$year = ( c::getPagePiece( 3 ) ) ? c::getPagePiece( 3 ) : date( 'Y', strtotime( '- 1 day' ) );
		$week = ( c::getPagePiece( 4 ) ) ? c::getPagePiece( 4 ) : date( 'W', strtotime( '- 1 day' ) );
		$firstDay = new DateTime( date( 'Y-m-d', strtotime( $year . 'W' . $week . 1 ) ), new DateTimeZone( c::config()->timezone  ) );
		if( date( 'l' ) == 'Monday' ){
			$firstDay->modify( '+ 1 week' );
		}
		$days = [];
		for( $i = 0; $i <= 6; $i++ ){
			$days[] = new DateTime( $firstDay->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
			$firstDay->modify( '+ 1 day' );
		}
		$firstDay->modify( '-1 day' );
		c::view()->to = new DateTime( $firstDay->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
		$firstDay->modify( '-6 day' );
		c::view()->from = new DateTime( $firstDay->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
		if( $week <= 1 ){
			$weekPrev = ( $year - 1 ) . '/52';
		} else {
			$weekPrev = ( $year ) . '/' . ( $week - 1 );
		}
		if( $week >= 52 ){
			$weekNext = ( $year + 1 ) . '/01';
		} else {
			$weekNext = ( $year ) . '/' . ( $week + 1 );
		}

		c::view()->weekPrev = $weekPrev;
		c::view()->weekNext = $weekNext;
		c::view()->days = $days;
		c::view()->week = $week;
		c::view()->year = $year;
		c::view()->display( 'drivers/shift/summary/index' );

	}

	public function summaryShift(){
		$id_shift = c::getPagePiece( 4 );
		$shift = Crunchbutton_Community_Shift::o( $id_shift );
		if( $shift->id_community_shift ){
			c::view()->shift = $shift;
			c::view()->layout( 'layout/ajax' );
			c::view()->display( 'drivers/shift/summary/shift' );	
		}
	}

	public function community(){
		
		$id_community = c::getPagePiece( 3 );
		$year = ( c::getPagePiece( 4 ) != '' ? c::getPagePiece( 4 ) : false );
		$week = ( c::getPagePiece( 5 ) != '' ? c::getPagePiece( 5 ) : false );

		if( $id_community && $year && $week ){

			if( intval( $week ) < 10 ){
				$week = '0' . intval( $week );
			}

			// Start week at monday #2666
			$firstDay = new DateTime( date( 'Y-m-d', strtotime( $year . 'W' . $week . 1 ) ), new DateTimeZone( c::config()->timezone  ) );

			$days = [];
			for( $i = 0; $i <= 6; $i++ ){
				$days[] = new DateTime( $firstDay->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
				$firstDay->modify( '+ 1 day' );
			}

			if( $week <= 1 ){
				$weekPrev = ( $year - 1 ) . '/52';
			} else {
				$weekPrev = ( $year ) . '/' . ( $week - 1 );
			}
			if( $week >= 52 ){
				$weekNext = ( $year + 1 ) . '/01';
			} else {
				$weekNext = ( $year ) . '/' . ( $week + 1 );
			}

			c::view()->weekPrev = $weekPrev;
			c::view()->weekNext = $weekNext;
			c::view()->days = $days;
			$firstDay->modify( '-1 day' );
			c::view()->to = new DateTime( $firstDay->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
			$firstDay->modify( '-6 day' );
			c::view()->from = new DateTime( $firstDay->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
			c::view()->id_community = $id_community;
		} else {
			$year = date( 'Y', strtotime( '- 1 day' ) );
			$week = date( 'W', strtotime( '- 1 day' ) );
			$day = new DateTime( date( 'Y-m-d', strtotime( $year . 'W' . $week . 1 ) ), new DateTimeZone( c::config()->timezone  ) );
			$day->modify( '+ 1 week' );
			$week = $day->format( 'W' );
		}
		c::view()->week = $week;
		c::view()->year = $year;
		if( $_REQUEST[ 'ajax' ] ){
			c::view()->ajax = true;
			c::view()->layout( 'layout/ajax' );	
		}
		
		c::view()->display( 'drivers/shift/community/index' );
	}

	public function statusShift(){

		$year = ( c::getPagePiece( 4 ) != '' ? c::getPagePiece( 4 ) : date( 'Y', strtotime( '- 1 day' ) ) );
		$week = ( c::getPagePiece( 5 ) != '' ? c::getPagePiece( 5 ) : date( 'W', strtotime( '- 1 day' ) ) );

		$day = new DateTime( date( 'Y-m-d', strtotime( $year . 'W' . $week . 1 ) ), new DateTimeZone( c::config()->timezone  ) );
		$from = new DateTime( $day->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
		$day->modify( '+6 day' );
		$to = new DateTime( $day->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
		

		$communities = Crunchbutton_Community_Shift::communitiesWithDeliveryService();

		c::view()->year = $year;
		c::view()->week = $week;
		c::view()->to = $to;
		c::view()->from = $from;
		c::view()->communities = $communities;
		c::view()->display( 'drivers/shift/status/index' );
	}

}