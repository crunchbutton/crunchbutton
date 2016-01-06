<?php

class Controller_api_shifts extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if( !c::admin()->permission()->check( ['global', 'support-all', 'support-view', 'support-crud' ] ) ){
			$this->error( 401 );
		}

		switch ( c::getPagePiece( 2 ) ) {
			case 'load-shifts':
				$this->_loadShifts();
				break;
			case 'load-shift':
				$this->_loadShift();
				break;
			case 'week-start':
				$this->_weekStart();
				break;
			case 'show-hide-shift':
				$this->_showHideShift();
				break;
			case 'add-shift':
				$this->_addShift();
				break;
		}
	}

	private function _addShift(){
		if( $this->method() == 'post' ){
			$id_community = $this->request()[ 'id_community' ];
			$date = $this->request()[ 'date' ];
			$segments = $this->request()[ 'hours' ];
			$type = $this->request()[ 'type' ];

			switch ( $type ) {
				case 'one-time-shift':
					$recurring = false;
					break;
				case 'repeat-every-week':
					$recurring = true;
					break;
				case 'repeat-every-day':
					$recurring = true;
					break;
			}

			$community = Crunchbutton_Community::o( $id_community );

			$timezone = $community->timezone;

			$date = DateTime::createFromFormat( 'Y-m-d H:i:s', $date . ' 00:00:00', new DateTimeZone( $timezone ) );

			$day = $date->format( 'd' );
			$month = $date->format( 'm' );
			$year = $date->format( 'Y' );

			if( !$id_community || !$day || !$month || !$year ){
				echo json_encode( [ 'error' => 'invalid object' ] );
				exit;
			}

			$hours = [];

			if( $type == 'repeat-every-day' ){

			} else {
				// add just the hour for the day
				$date_base = DateTime::createFromFormat( 'Y-m-d H:i:s', $year . '-' . $month . '-' . $day . ' 00:00:00', new DateTimeZone( $timezone ) );
				$_hours = Crunchbutton_Admin_Hour::segmentToDate( $date_base, $segments, $timezone );
				if( $_hours ){
					$hours[] = [ 'start' => $_hours[ 'start' ], 'end' => $_hours[ 'end' ] ];
				}
			}

			foreach( $hours as $hour ){
				$shift = new Crunchbutton_Community_Shift();
				$shift->id_community = $id_community;
				$shift->date_start = $hour[ 'start' ];
				$shift->date_end = $hour[ 'end' ];
				$shift->recurring = $recurring;
				$shift->active = 1;
				if( $shift->date_start && $shift->date_end ){
					$shift->save();
				}
			}
			echo json_encode( [ 'success' => $shift->id_community ] );exit;
		}
		echo json_encode( [ 'error' => true ] );exit;
	}

	private function _loadShift(){
		$shift = Community_Shift::o( c::getPagePiece( 3 ) );
		if( $shift->id_community_shift ){
			$out = [];
			echo json_encode( $out );exit;
		} else {
			$this->error( 404 );
		}
	}

	private function _showHideShift(){

		if( $this->method() == 'post' ){
			$id_community_shift = $this->request()[ 'id_community_shift' ];
			$shift = Community_Shift::o( $id_community_shift );
			if( $shift->id_community_shift ){
				$shift->hidden = ( $shift->hidden ) ? 0 : 1;
				$shift->save();
				echo json_encode( [ 'success' => true ] );exit;
			}
		}
		echo json_encode( [ 'error' => true ] );exit;
	}

	private function _weekStart(){
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone  ) );
		$_now = $now->format( 'M jS Y' );
		if( $now->format( 'l' ) == 'Thursday' ){
			$thursday = $now;
		} else {
			$thursday = new DateTime( 'last thursday', new DateTimeZone( c::config()->timezone  ) );
		}
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$range = [ 'start' => $thursday->format( 'Y,m,d' ) ];
		echo json_encode( $range );
	}

	private function _loadShifts(){

		$out = [ 'days' => [] ];

		$start = ( new DateTime( $this->request()['start'] ) );
		$filterCommunities = $this->request()['communities'];
		// @remove -- remove it before commit
		$filterCommunities = [ 92, 29 ];

		$year = ( $this->request()['year'] ? $this->request()['year'] : $start->format( 'Y' ) );
		$month = ( $this->request()['month'] ? $this->request()['month'] : $start->format( 'm' ) );
		$day = ( $this->request()['day'] ? $this->request()['day'] : $start->format( 'd' ) );

		if( $year == $start->format( 'Y' ) && $month == $start->format( 'm' ) && $day == $start->format( 'd' ) ){
			$current = true;
		} else {
			$current = false;
		}

		// Start week on thursday
		$firstDay = new DateTime( $year . '-' . $month . '-' . $day, new DateTimeZone( c::config()->timezone  ) );

		$days = [];
		for( $i = 0; $i <= 6; $i++ ){
			$days[] = new DateTime( $firstDay->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
			$out[ 'days' ][ $firstDay->format( 'Ymd' ) ] = [ 'date' => $firstDay->format( 'M jS' ), 'weekday' => $firstDay->format( 'l' ) ];
			$firstDay->modify( '+ 1 day' );
		}

		// prev/next links
		$firstDay->modify( '- 2 week' );
		$link_prev_day = $firstDay->format( 'Y/m/d' );
		$firstDay->modify( '+ 2 week' );
		$link_next_day = $firstDay->format( 'Y/m/d' );

		$communities = [];

		foreach( $filterCommunities as $community ) {
			$community = Community::o( $community );
			if( $community->id_community ){
				$shifts = [];
				foreach( $days as $day ) {
					$shifts[ $day->format( 'Ymd' ) ] = [ 'shifts' => [], 'date' => [ 'day' => $day->format( 'Y-m-d' ), 'formatted' => $day->format( 'M jS Y' ), 'tz' => $community->timezone ] ];
				}
				$communities[ $community->id_community ] = [ 'id_community' => $community->id_community, 'name' => $community->name, 'days' => $shifts ];
			}
		}

		usort( $communities, function( $a, $b ) {
			return $a[ 'name' ] > $b[ 'name' ];
		} );

		$_communities = [];

		foreach( $communities as $community ){
			$_communities[ $community[ 'id_community' ] ] = $community;
		}

		$communities = $_communities;

		foreach( $days as $day ) {
			$segments = Crunchbutton_Community_Shift::shiftsByDay( $day->format( 'Y-m-d' ) );
			foreach( $segments as $segment ){
				if( $communities[ $segment->id_community ] ){
					$communities[ $segment->id_community ]['days'][ $day->format( 'Ymd' ) ][ 'shifts' ][] = $this->_parseSegment( $segment );
				}
			}
		}

		// prev/next links
		$firstDay->modify( '- 2 week' );
		$out[ 'prev' ] = $firstDay->format( 'Y/m/d' );
		$firstDay->modify( '+ 2 week' );
		$out[ 'next' ] = $firstDay->format( 'Y/m/d' );

		$firstDay->modify( '-1 day' );
		$to = new DateTime( $firstDay->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
		$firstDay->modify( '-6 day' );
		$from = new DateTime( $firstDay->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
		$out[ 'period' ] = [ 'to' => $to->format( 'M jS Y' ), 'from' => $from->format( 'M jS Y' ) ];

		$out[ 'year' ] = $year;
		$out[ 'month' ] = $month;
		$out[ 'day' ] = $day;
		$out[ 'current' ] = $current;
		$out[ 'now' ] = $_now;
		$out[ 'communities' ] = $communities;

		echo json_encode( $out );exit;
	}

	private function _parseSegment( $segment ){
		$out = [
						'id_community_shift' => $segment->id_community_shift,
						// 'full_date' => $segment->fullDate(),
						'period' => $segment->startEndToString(),
						'tz' => $segment->timezoneAbbr(),
						// 'full_date_pst' => $segment->fullDate( c::config()->timezone ),
						'period_pst' => $segment->startEndToString( Community_Shift::CB_TIMEZONE ),
		 				];

		 if( $segment->isHidden() ){
		 	$out[ 'hidden' ] = true;
		 }

		 if( $segment->id_community_shift_father || $segment->recurring > 0 ){
		 	$out[ 'recurring' ] = true;
		 }

		$_drivers = $segment->getDrivers();

		$firstDayOfWeek = $segment->firstDayOfWeek()->format( 'Y-m-d' );
		$lastDayOfWeek = $segment->lastDayOfWeek()->format( 'Y-m-d' );

		$drivers = [];
		foreach( $_drivers as $driver ){

			$_driver = [];
			if( ( Crunchbutton_Admin_Shift_Assign_Permanently::adminIsPermanently( $driver->id_admin, $segment->id_community_shift ) ) ){
				$_driver[ 'permanent' ] = true;
			}
			if( Crunchbutton_Admin_Shift_Assign::isFirstWeek( $driver->id_admin, $segment->dateStart()->format( 'Y-m-d H:i' )  ) ){
			 $_driver[ 'first_week' ] = true;
			}
			$orders_per_hour = $driver->ordersPerHour();
			if( $orders_per_hour ){
				$_driver[ 'orders_per_hour' ] = $orders_per_hour;
			}

			$_driver[ 'id_admin' ] = $driver->id_admin;
			$_driver[ 'name' ] = $driver->name;

			$drivers[] = $_driver;
		}

		if( count( $drivers ) ){
			$out[ 'drivers' ] = $drivers;
		}
		return $out;
	}

}
