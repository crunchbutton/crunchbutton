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

				if ( c::admin()->permission()->check( [ 'global','drivers-all', 'drivers-working-hours', 'drivers-working-hours-view' ] ) || c::admin()->isDriver() ) {
					switch ( c::getPagePiece( 3 ) ) {
						case 'driver':
							$this->scheduleDriver();
						break;
						default:
						break;
					}
				}
				break;

			case 'status':

				// header('HTTP/1.1 301 Moved Permanently');
				// header('Location: https://cockpit.la/drivers/onboarding');

				if ( c::admin()->permission()->check( [ 'global' ] ) ){
					switch ( c::getPagePiece( 3 ) ) {
						case 'shift':
							$this->statusShift();
						break;
						default:
						break;
					}
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

		// Start week on Thursday #3084
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone  ) );
		if( $now->format( 'l' ) == 'Thursday' ){
			$thursday = $now;
			$thursday->modify( '+ 1 week' );
		} else {
			$thursday = new DateTime( 'next thursday', new DateTimeZone( c::config()->timezone  ) );
		}

		$year = ( c::getPagePiece( 4 ) != '' ? c::getPagePiece( 4 ) : $thursday->format( 'Y' ) );
		$month = ( c::getPagePiece( 5 ) != '' ? c::getPagePiece( 5 ) : $thursday->format( 'm' ) );
		$day = ( c::getPagePiece( 6 ) != '' ? c::getPagePiece( 6 ) : $thursday->format( 'd' ) );

		// Start week on thursday
		$firstDay = new DateTime( $year . '-' . $month . '-' . $day, new DateTimeZone( c::config()->timezone  ) );

		$first_day_year = $firstDay->format( 'Y' );
		$first_day_week = $firstDay->format( 'W' );

		$days = [];
		for( $i = 0; $i <= 6; $i++ ){
			$days[] = new DateTime( $firstDay->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
			$firstDay->modify( '+ 1 day' );
		}
		// prev/next links
		$firstDay->modify( '- 2 week' );
		$link_prev_day = $firstDay->format( 'Y/m/d' );
		$firstDay->modify( '+ 2 week' );
		$link_next_day = $firstDay->format( 'Y/m/d' );

		c::view()->week = $first_day_week;
		c::view()->year = $first_day_year;
		c::view()->link_prev = $link_prev_day;
		c::view()->link_next = $link_next_day;
		c::view()->status = Crunchbutton_Admin_Shift_Status::getByAdminWeekYear( $admin->id_admin, $first_day_week, $first_day_year );
		c::view()->days = $days;
		c::view()->from = $days[ 0 ];
		c::view()->to = $days[ 6 ];
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

		// Start week on Thursday #3084
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone  ) );
		$_now = $now->format( 'M jS Y' );
		if( $now->format( 'l' ) == 'Thursday' ){
			$thursday = $now;
		} else {
			$thursday = new DateTime( 'last thursday', new DateTimeZone( c::config()->timezone  ) );
			// $thursday->modify( '+ 1 week' );
		}

		$year = ( c::getPagePiece( 3 ) != '' ? c::getPagePiece( 3 ) : $thursday->format( 'Y' ) );
		$month = ( c::getPagePiece( 4 ) != '' ? c::getPagePiece( 4 ) : $thursday->format( 'm' ) );
		$day = ( c::getPagePiece( 5 ) != '' ? c::getPagePiece( 5 ) : $thursday->format( 'd' ) );

		if( $year == $thursday->format( 'Y' ) && $month == $thursday->format( 'm' ) && $day == $thursday->format( 'd' ) ){
			$current = true;
		} else {
			$current = false;
		}

		// Start week on thursday
		$firstDay = new DateTime( $year . '-' . $month . '-' . $day, new DateTimeZone( c::config()->timezone  ) );

		$days = [];
		for( $i = 0; $i <= 6; $i++ ){
			$days[] = new DateTime( $firstDay->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
			$firstDay->modify( '+ 1 day' );
		}

		// prev/next links
		$firstDay->modify( '- 2 week' );
		$link_prev_day = $firstDay->format( 'Y/m/d' );
		$firstDay->modify( '+ 2 week' );
		$link_next_day = $firstDay->format( 'Y/m/d' );

		$firstDay->modify( '-1 day' );
		c::view()->to = new DateTime( $firstDay->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
		$firstDay->modify( '-6 day' );
		c::view()->from = new DateTime( $firstDay->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );

		c::view()->link_prev = $link_prev_day;
		c::view()->link_next = $link_next_day;
		c::view()->days = $days;
		c::view()->week = $week;
		c::view()->year = $year;
		c::view()->current = $current;
		c::view()->now = $_now;
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
		$month = ( c::getPagePiece( 5 ) != '' ? c::getPagePiece( 5 ) : false );
		$day = ( c::getPagePiece( 6 ) != '' ? c::getPagePiece( 6 ) : false );

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone  ) );
		if( $now->format( 'l' ) == 'Thursday' ){
			$thursday = $now;
		} else {
			$thursday = new DateTime( 'last thursday', new DateTimeZone( c::config()->timezone  ) );
			$thursday->modify( '+ 1 week' );
		}
		if( $year == $thursday->format( 'Y' ) && $month == $thursday->format( 'm' ) && $day == $thursday->format( 'd' ) ){
			$current = true;
		} else {
			$current = false;
		}

		if( $id_community && $year && $month && $day ){

			// Start week on thursday
			$firstDay = new DateTime( $year . '-' . $month . '-' . $day, new DateTimeZone( c::config()->timezone  ) );

			$start_date = $firstDay->format( 'Y/m/d' );

			// prev/next links
			$link_start_day = $firstDay->format( 'Y/m/d' );
			$firstDay->modify( '- 1 week' );
			$link_prev_day = $firstDay->format( 'Y/m/d' );
			$firstDay->modify( '+ 2 week' );
			$link_next_day = $firstDay->format( 'Y/m/d' );
			$firstDay->modify( '- 1 week' );

			$days = [];
			for( $i = 0; $i <= 6; $i++ ){
				$days[] = new DateTime( $firstDay->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
				$firstDay->modify( '+ 1 day' );
			}

			c::view()->start_date = $start_date;
			c::view()->link_prev = $link_prev_day;
			c::view()->link_next = $link_next_day;
			c::view()->current = $current;
			c::view()->days = $days;

			$firstDay->modify( '-1 day' );
			c::view()->to = new DateTime( $firstDay->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
			$firstDay->modify( '-6 day' );
			c::view()->from = new DateTime( $firstDay->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );

			c::view()->id_community = $id_community;
		} else {

			// Start week on Thursday #3084
			$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone  ) );
			if( $now->format( 'l' ) == 'Thursday' ){
				$thursday = $now;
			} else {
				$thursday = new DateTime( 'last thursday', new DateTimeZone( c::config()->timezone ) );
			}
			$start_date = $thursday->format( 'Y/m/d' );
			$day = $thursday;
		}

		c::view()->start_date = $start_date;

		if( $_REQUEST[ 'ajax' ] ){
			c::view()->ajax = true;
			c::view()->layout( 'layout/ajax' );
		}

		c::view()->display( 'drivers/shift/community/index' );
	}

	public function statusShift(){

		// Start week on Thursday #3084
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone  ) );
		if( $now->format( 'l' ) == 'Thursday' ){
			$thursday = $now;
		} else {
			$thursday = new DateTime( 'last thursday', new DateTimeZone( c::config()->timezone  ) );
		}

		$year = ( c::getPagePiece( 4 ) != '' ? c::getPagePiece( 4 ) : $thursday->format( 'Y' ) );
		$month = ( c::getPagePiece( 5 ) != '' ? c::getPagePiece( 5 ) : $thursday->format( 'm' ) );
		$day = ( c::getPagePiece( 6 ) != '' ? c::getPagePiece( 6 ) : $thursday->format( 'd' ) );

		if( $year == $thursday->format( 'Y' ) && $month == $thursday->format( 'm' ) && $day == $thursday->format( 'd' ) ){
			$current = true;
		} else {
			$current = false;
		}

		// Start week on thursday
		$firstDay = new DateTime( $year . '-' . $month . '-' . $day, new DateTimeZone( c::config()->timezone  ) );

		c::view()->week = $firstDay->format( 'W' );
		c::view()->year = $firstDay->format( 'Y' );

		$from = new DateTime( $firstDay->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
		$firstDay->modify( '+6 day' );
		$to = new DateTime( $firstDay->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
		$firstDay->modify( '- 6 days' );

		$communities = Crunchbutton_Community_Shift::communitiesWithDeliveryService();

		// prev/next links
		$firstDay->modify( '- 1 week' );
		$link_prev_day = $firstDay->format( 'Y/m/d' );
		$firstDay->modify( '+ 2 week' );
		$link_next_day = $firstDay->format( 'Y/m/d' );

		c::view()->link_prev = $link_prev_day;
		c::view()->link_next = $link_next_day;
		c::view()->to = $to;
		c::view()->from = $from;
		c::view()->current = $current;
		c::view()->communities = $communities;
		c::view()->display( 'drivers/shift/status/index' );
	}
}