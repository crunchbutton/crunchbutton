<?php

class Crunchbutton_Hour extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('hour')
			->idVar('id_hour')
			->load($id);
	}

	public function restaurantNextCloseTime( $restaurant ){
		$today = new DateTime( 'now', new DateTimeZone( $restaurant->timezone ) );
		$day = strtolower( $today->format( 'D' ) );
		$hours = Hour::getByRestaurantWeek( $restaurant, false );
		foreach( $hours as $hour ){
			if( $hour->status == 'close' ){
				$open = new DateTime( $hour->from, new DateTimeZone( $restaurant->timezone ) );
				if( $open >= $today ){
					return $open;
				}
			}
		}
		return false;
	}

	public function restaurantNextOpenTime( $restaurant ){
		$today = new DateTime( 'now', new DateTimeZone( $restaurant->timezone ) );
		$day = strtolower( $today->format( 'D' ) );
		$hours = Hour::getByRestaurantWeek( $restaurant, false );
		foreach( $hours as $hour ){
			if( $hour->status == 'open' ){
				$open = new DateTime( $hour->from, new DateTimeZone( $restaurant->timezone ) );
				if( $open >= $today ){
					return $open;
				}
			}
		}
		return false;
	}

	public function restaurantClosesIn( $restaurant ){
		$today = new DateTime( 'now', new DateTimeZone( $restaurant->timezone ) );
		$close = Hour::restaurantNextCloseTime( $restaurant );
		if( $close ){
			$interval = $today->diff( $close );
			$minutes = ( $interval->m * 30 * 24 * 60 ) + ( $interval->d * 24 * 60 ) + ( $interval->h * 60 ) + ( $interval->i );
			if( $minutes > 0 ){
				return $minutes;
			} 
		}
		return false;
	}

	public function restaurantOpensIn( $restaurant ){
		$today = new DateTime( 'now', new DateTimeZone( $restaurant->timezone ) );
		$open = Hour::restaurantNextOpenTime( $restaurant );
		if( $open ){
			$interval = $today->diff( $open );
			$minutes = ( $interval->m * 30 * 24 * 60 ) + ( $interval->d * 24 * 60 ) + ( $interval->h * 60 ) + ( $interval->i );
			if( $minutes > 0 ){
				return $minutes;
			} 
		}
		return false;
	}

	public function restaurantIsOpen( $restaurant, $dt = null ){

		$time = ( $dt ? $dt : 'now' );
		$today = new DateTime( $time, new DateTimeZone( $restaurant->timezone ) );
		$day = strtolower( $today->format( 'D' ) );

		$hours = $restaurant->hours();

		foreach ( $hours as $hour ) {

			$hasHours = true;

			if ( $hour->day != $day ) {
				continue;
			}

			if( $dt ){
				$open  = new DateTime( $today->format( 'Y-m-d' ) . ' ' . $hour->time_open,  new DateTimeZone( $restaurant->timezone ) );
				$close = new DateTime( $today->format( 'Y-m-d' ) . ' ' . $hour->time_close, new DateTimeZone( $restaurant->timezone ) );	
			} else {
				$open  = new DateTime( 'today ' . $hour->time_open,  new DateTimeZone( $restaurant->timezone ) );
				$close = new DateTime( 'today ' . $hour->time_close, new DateTimeZone( $restaurant->timezone ) );	
			}

			// if closeTime before openTime, then closeTime should be for tomorrow
			if ( $close->getTimestamp() < $open->getTimestamp() ) {
				date_add( $close, date_interval_create_from_date_string( '1 day' ) );
			}

			if ( $today->getTimestamp() >= $open->getTimestamp() && $today->getTimestamp() <= $close->getTimestamp() ) {
				return true;
			}
		}

		return false;
	}

	public function hoursByRestaurant( $restaurant, $gmt = false ){
		if ( !isset( $restaurant->_hours[ $gmt ] ) ) {
			$hours = Hour::q( "SELECT * FROM hour WHERE id_restaurant = {$restaurant->id_restaurant}" );
			if ( $gmt ) {
				$timezone = new DateTime( 'now ', new DateTimeZone( $restaurant->timezone ) );
				$timezone = $timezone->format( 'O' );
				foreach ( $hours as $hour ) {
					$open = new DateTime( 'next '.$hour->day. ' ' .$hour->time_open, new DateTimeZone( $restaurant->timezone ) );
					$open->setTimezone( new DateTimeZone( 'GMT' ) );
					$close = new DateTime( 'next '.$hour->day. ' ' .$hour->time_close, new DateTimeZone( $restaurant->timezone ) );
					$close->setTimezone( new DateTimeZone('GMT' ) );
					$hour->time_open = $open->format( 'Y-m-d H:i' );
					$hour->time_close = $close->format( 'Y-m-d H:i' );
				}
			}
			$restaurant->_hours[ $gmt ] = $hours;
		}
		return $restaurant->_hours[ $gmt ] ;
	}

	public function getByRestaurantWeek( $restaurant, $utc = true ){
		return Hour::getByRestaurantToExport( $restaurant, $utc );
	}

	public function getByRestaurantNext24Hours( $restaurant, $utc = true ){
		return Hour::getByRestaurantToExport( $restaurant, $utc, true );
	}

	public function getRestaurantRegularPlusHolidayHours( $restaurant ){
		
		// Get the restaurant's regular hours
		$hours = $restaurant->hours();

		// empty array to store the merged hours
		$_hours = [];

		// Convert the hours to a simple array
		foreach ( $hours as $hour ) {
			$_hours[ $hour->day ][] = [ $hour->time_open, $hour->time_close ];
		}

		// Merge the restaurant hours with the holidays
		return Hour::mergeHolidays( $_hours, $restaurant );
	}

	public function getByRestaurantToExport( $restaurant, $utc = true, $next24hours = false ){

		$hours = Hour::getRestaurantRegularPlusHolidayHours( $restaurant );

		if( count( $hours ) == 0 ){
			return $hours;
		}

		$getDay = new DateTime( 'now', new DateTimeZone( ( $utc ? 'GMT' : $restaurant->timezone ) ) );
			
		// step back two days
		$getDay->modify( '-2 day' );

		// loop to get all the days of the week, starting by yestarday
		for( $i = 0; $i <= 6; $i++ ){

			$getDay->modify( '+1 day' );
			$actualDay = strtolower( $getDay->format( 'D' ) );

			foreach( $hours as $day => $segments ){

				// get the days in sequence
				if( $day != $actualDay ){ continue; }

				// loop to get all the segments
				foreach( $segments as $times ){

					// create a Datetime to the start time
					$start = new DateTime( $getDay->format( 'Y-m-d' ) . ' ' . $times[ 'from' ], new DateTimeZone( $restaurant->timezone ) );
					// Convert to UTC/GMT case it is needed 
					if( $utc ){
						$start->setTimezone( new DateTimeZone( 'GMT' ) );	
					}

					// create a Datetime to the end time
					$end = new DateTime( $getDay->format( 'Y-m-d' ) . ' ' . $times[ 'to' ], new DateTimeZone( $restaurant->timezone ) );
					// Convert to UTC/GMT case it is needed 
					if( $utc ){
						$end->setTimezone( new DateTimeZone( 'GMT' ) );
					}

					// it means it ends in another day, so add the needed days
					if( $times[ 'to_days' ] ){
						$end->modify( '+' . $times[ 'to_days' ] . ' day' );
					}

					// get the right format
					$from = $start->format( 'Y-m-d H:i' );
					$to = $end->format( 'Y-m-d H:i' );

					// create an array to store the info
					$data = array( 'from' => $from, 'to' => $to, 'status' => $times[ 'status' ] );
					if( $times[ 'notes' ] ){
						$data[ 'notes' ] = $times[ 'notes' ];
					}
					$_hours_utc[] = ( object ) $data;	
				}
			}			
		}
		if( $next24hours ){

			$_hours = [];

			$now = new DateTime( 'now', new DateTimeZone( ( $utc ? 'GMT' : $restaurant->timezone ) ) );
			$now_plus_24 = new DateTime( 'now', new DateTimeZone( ( $utc ? 'GMT' : $restaurant->timezone ) ) );
			$now_plus_24->modify( '+1 day' );
			foreach ( $_hours_utc as $hour ) {
				
				$data = false;

				$from = new DateTime( $hour->from, new DateTimeZone( ( $utc ? 'GMT' : $restaurant->timezone ) ) );
				$to = new DateTime( $hour->to, new DateTimeZone( ( $utc ? 'GMT' : $restaurant->timezone ) ) );

				// case 1
				if( $from <= $now && $to <= $now_plus_24 && $to > $now ){
					$data = array( 'from' => $now->format( 'Y-m-d H:i' ), 'to' => $hour->to );
				} 
				// case 2
				else if( $from <= $now && $to >= $now_plus_24 ){
					$data = array( 'from' => $now->format( 'Y-m-d H:i' ), 'to' => $now_plus_24->format( 'Y-m-d H:i' ) );
				}
				// case 3
				else if( $from >= $now && $to >= $now_plus_24 && $from < $now_plus_24 ){
					$data = array( 'from' => $hour->from, 'to' => $now_plus_24->format( 'Y-m-d H:i' ) );
				}
				// case 4
				else if( $from >= $now && $to <= $now_plus_24  ){
					$data = array( 'from' => $hour->from, 'to' => $hour->to );
				}

				if( $data ){
					$data[ 'status' ] = $hour->status;
					if( $hour->notes ){
						$data[ 'notes' ] = $hour->notes;
					}
					
					$_hours[] = ( object ) $data;
				}

			}

			// return the last 24 hours
			return $_hours;

		} else {
			// Return the whole week
			return $_hours_utc;
		}
		
	}

	// Legacy method
	public function hoursStartingMondayUTC( $hours ){
		
		if( count( $hours ) == 0 ){
			return $hours;
		}

		$weekdays = [ 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun' ];

		$monday = date( 'Y-m-d', strtotime( 'monday this week' ) );

		$_hours_utc = [];
		foreach( $hours as $day => $segments ){
			foreach( $segments as $times ){
				$open = new DateTime('next '. $day . ' ' . $times[0], new DateTimeZone($this->timezone));
				$close = new DateTime('next '.$day. ' ' . $times[1], new DateTimeZone($this->timezone));
				if( $open->format('Hi') > $close->format('Hi') ){
					$close->modify('+1 day');
				}
				$close->setTimezone(new DateTimeZone('GMT'));
				$open->setTimezone(new DateTimeZone('GMT'));
				$hour_open = $open->format('H:i');
				$hour_close = $close->format('H:i');
				if( !$_hours_utc[ $day ] ){
					$_hours_utc[ $day ] = [];
				}
				$open_day = strtolower( $open->format( 'D' ) );
				$close_day = strtolower( $close->format( 'D' ) );
				$_hours_utc[ $day ][] = [ 'open' => $hour_open, 'open_day' => $open_day, 'close_day' => $close_day, 'close' => $hour_close, 'open_datetime' => $open->getTimestamp(), 'close_datetime' => $close->getTimestamp() ];
			}
		}

		// Convert to hours starting at monday
		$_hours = [];

		foreach( $_hours_utc as $day => $segments ){

			foreach( $segments as $times ){
				
				$open_dayshours = array_search( $times[ 'open_day' ], $weekdays ) * 2400;
				$close_dayshours = array_search( $times[ 'close_day' ], $weekdays ) * 2400;
				preg_match( '/(\d+):(\d+)/', $times[ 'open' ], $hour_open );
				preg_match( '/(\d+):(\d+)/', $times[ 'close' ], $hour_close );
				$hour_open = ( $open_dayshours + intval( $hour_open[ 1 ] ) * 100 ) + intval( $hour_open[ 2 ] );
				$hour_close = ( $close_dayshours + intval( $hour_close[ 1 ] ) * 100 ) + intval( $hour_close[ 2 ] );
				// it opens at sunday and closes at monday
				if( $hour_close < $hour_open && $times[ 'open_day' ] == 'sun' ){

					while( $hour_close < $hour_open ){
						$hour_close += 2400;
					}
					$hour_close = ( $hour_close - 16800 );
					$_hours[] = [ 'open' => $hour_open, 'close' => 16800 ];
					if( $hour_close != '0' ){
						$_hours[] = [ 'open' => 0, 'close' => $hour_close ];	
					}
				} else {
					$_hours[] = [ 'open' => $hour_open, 'close' => $hour_close ];
				}
			}
		}
		// echo '<pre>'; var_dump( $_hours ); exit;
		return $_hours;

	}

	// This method merge restaurant hours with the holidays
	public function mergeHolidays( $hours, $restaurant, $convertHours = true ){

		if( count( $hours ) == 0 ){
			return $hours;
		}

		$weekdays = [ 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun' ];
		
		// Convert to hours starting at monday - it is easy to merge -- example a time like 234 indicating 2:34 AM
		$hoursStartFinish = [];
		foreach( $hours as $day => $segments ){
			$dayshours = array_search( $day, $weekdays ) * 2400;
			foreach( $segments as $times ){
				preg_match( '/(\d+):(\d+)/', $times[ 0 ], $hour_open );
				preg_match( '/(\d+):(\d+)/', $times[ 1 ], $hour_close );
				$hour_open = ( $dayshours + intval( $hour_open[ 1 ] ) * 100 ) + intval( $hour_open[ 2 ] );
				$hour_close = ( $dayshours + intval( $hour_close[ 1 ] ) * 100 ) + intval( $hour_close[ 2 ] );
				// it is closing at midnight
				if( $hour_close < $hour_open ){
					$hour_close += 1200;
				}
				$hoursStartFinish[] = [ 'open' => $hour_open, 'close' => $hour_close ];
			}
		}

		// Get the monday and sunday of the current week
		$monday = date( 'Y-m-d', strtotime( 'monday this week' ) );
		$sunday = date( 'Y-m-d', strtotime( 'sunday this week' ) );
		
		// Get the hollidays of the current week
		$overrides = Crunchbutton_Restaurant_Hour_Override::q( "SELECT * FROM restaurant_hour_override 
																															WHERE id_restaurant = {$restaurant->id_restaurant} 
																															AND (
																															( DATE_FORMAT( date_start, '%Y-%m-%d' ) >= '{$monday}' AND DATE_FORMAT( date_start, '%Y-%m-%d' ) <= '{$sunday}' )
																															OR 
																															( DATE_FORMAT( date_start, '%Y-%m-%d' ) < '{$monday}' AND DATE_FORMAT( date_end, '%Y-%m-%d' ) > '{$monday}' )	) " );
		$hoursStartFinishOverrideClose = [];
		if( $overrides->count() ){

			foreach( $overrides as $override ){

				$monday = new DateTime( date( 'Y-m-d H:i:s', strtotime( 'monday this week' ) ), new DateTimeZone( $restaurant->timezone ) );
				$date_start = new DateTime( $override->date_start, new DateTimeZone( $restaurant->timezone ) );
				
				// Limit the override to this week
				if( $date_start < $monday ){
					$date_start = $monday;
				}
				$dayshours = array_search( strtolower( $date_start->format( 'D' ) ), $weekdays ) * 2400;
				$hour_open = ( $dayshours + intval( $date_start->format( 'H' ) ) * 100 ) + intval( $date_start->format( 'i' ) );

				$sunday = new DateTime( date('Y-m-d H:i:s', strtotime('sunday this week') ), new DateTimeZone( $restaurant->timezone ) );
				$sunday->setTime( 23, 59 );
				$date_end = new DateTime( $override->date_end, new DateTimeZone( $restaurant->timezone ) );
				// Limit the override to this week
				if( $date_end > $sunday ){
					$date_end = $sunday;
				}
				$dayshours = array_search( strtolower( $date_end->format( 'D' ) ), $weekdays ) * 2400;

				$hour_close = ( $dayshours + intval( $date_end->format( 'H' ) ) * 100 ) + intval( $date_end->format( 'i' ) );
				if( $override->type == Crunchbutton_Restaurant_Hour_Override::TYPE_CLOSED ){
					$hoursStartFinishOverrideClose[] = [ 'start' => $hour_open, 'end' => $hour_close, '_start' => $date_start->format( 'Y-m-d H:i:s' ), '_data_end' => $date_end->format( 'Y-m-d H:i:s' ), 'notes' => $override->notes ];	
				} else if( $override->type == 'open' ){
					// Merge the override open hours at the open/close array
					$hoursStartFinish[] = [ 'open' => $hour_open, 'close' => $hour_close ];
				}
			}
		}

		// Sort the hours
		$hoursStartFinish = Cana_Util::sort_col( $hoursStartFinish, 'open' );

		// Merge the regular hours 
		// this foreach returns a array with the hours merged like: [ [ 'open' => 100, 'close' => 2300 ], [ 'open' => 3830, 'close' => 5050 ] ]
		foreach( $hoursStartFinish as $key => $val ){
			$getNext = false;
			foreach( $hoursStartFinish as $keyNext => $valNext ){
				if( $getNext ){
					if( $hoursStartFinish[ $keyNext ][ 'open' ] <= $hoursStartFinish[ $key ][ 'close' ] 
							&& $hoursStartFinish[ $keyNext ][ 'close' ] - $hoursStartFinish[ $key ][ 'open' ] < 3600 ) {
						$hoursStartFinish[ $key ][ 'close' ] = $hoursStartFinish[ $keyNext ][ 'close' ];
						unset( $hoursStartFinish[ $keyNext ] );
						$getNext = false;
					}
				}
				if( $key == $keyNext ){
					$getNext = true;
				}
			}
		}

		foreach( $hoursStartFinishOverrideClose as $keyClose => $valClose ){

			$force_close_start = $valClose[ 'start' ];
			$force_close_end = $valClose[ 'end' ];

			foreach( $hoursStartFinish as $keyOpen => $valOpen ){

				$regular_start = $hoursStartFinish[ $keyOpen ][ 'open' ];
				$regular_end = $hoursStartFinish[ $keyOpen ][ 'close' ];

				// case 1
				if( $force_close_start <= $regular_start && $force_close_end >= $regular_end ){
					unset( $hoursStartFinish[ $keyOpen ] );
				} else
				// case 2
				if( $force_close_start > $regular_start && $force_close_end < $regular_end ){
					$hoursStartFinish[ $keyOpen ][ 'close' ] = Cana_Util::sum_minutes( $force_close_start, 1 );
					$hoursStartFinish[] = [ 'open' => Cana_Util::sum_minutes( $force_close_end, 1 ), 'close' => $regular_end ];	
				} else
				// case 3
				if( $force_close_start <= $regular_start && $force_close_end <= $regular_end && $force_close_end > $regular_start ){
					$hoursStartFinish[ $keyOpen ][ 'open' ] = Cana_Util::sum_minutes( $force_close_end, 1 );
				} else
				// case 4
				if( $force_close_start >= $regular_start && $force_close_end >= $regular_end && $force_close_start < $regular_end ){
					$hoursStartFinish[ $keyOpen ][ 'close' ] = Cana_Util::sum_minutes( $force_close_start, 1 );
				}

			}
		}

		// Sort
		$hoursStartFinish = Cana_Util::sort_col( $hoursStartFinish, 'open' );
		
		// Merge the hours again
		foreach( $hoursStartFinish as $key => $val ){
			$getNext = false;
			foreach( $hoursStartFinish as $keyNext => $valNext ){
				if( $getNext ){
					if( $hoursStartFinish[ $keyNext ][ 'open' ] <= $hoursStartFinish[ $key ][ 'close' ] 
							&& $hoursStartFinish[ $keyNext ][ 'close' ] - $hoursStartFinish[ $key ][ 'open' ] < 3600 ) {
						$hoursStartFinish[ $key ][ 'close' ] = $hoursStartFinish[ $keyNext ][ 'close' ];
						unset( $hoursStartFinish[ $keyNext ] );
						$getNext = false;
					}
				}
				if( $key == $keyNext ){
					$getNext = true;
				}
			}
		}

		// Fill the hours array with the closed hours -- it should start with monday 00:00 and end at sunday 24:00
		$_hours = [];
		$last_close = false;

		foreach( $hoursStartFinish as $hour ){
			if( !$last_close && intval( $hour[ 'open' ] ) != 0 ){
				$last_close = -1;
			}
			if( $last_close !== false ){
				if( $hour[ 'open' ] != $last_close ){
					$_to = Cana_Util::subtract_minutes( $hour[ 'open' ], 1 );
					$_from = Cana_Util::sum_minutes( $last_close, 1 );
					$_hours[] = array( 'from' => $_from , 'to' => $_to, 'status' => 'close' );						
				}
			}
			$last_close = $hour[ 'close' ];
			$_hours[] = array( 'from' => $hour[ 'open' ], 'to' => $hour[ 'close' ], 'status' => 'open' );
		}

		// check if it is closing at 16800 -- sunday 24:00
		if( $last_close && $last_close < 16800 ){
			$_from = Cana_Util::sum_minutes( $last_close, 1 );
			$_hours[] = array( 'from' => $_from , 'to' => 16800, 'status' => 'close' );	
		}

		// Put the closed message notes
		foreach( $_hours as $key => $hour ){
			if( $_hours[ $key ][ 'status' ] == 'close' ){
				foreach( $hoursStartFinishOverrideClose as $closedHour ){
					if( $_hours[ $key ][ 'from' ] <= $closedHour[ 'start' ] && $_hours[ $key ][ 'to' ] >= $closedHour[ 'end' ] ){
						$_hours[ $key ][ 'notes' ] = $closedHour[ 'notes' ];
					}
				}
			}
		}

		// Legacy code
		if( !$convertHours ){
			$hours = [];
			$atSundayItWillClose = 0;
			foreach( $_hours as $key => $val ){
				if( $_hours[ $key ][ 'status' ] != 'open' ){
					continue;
				}
				$open = $_hours[ $key ][ 'from' ];
				$close = $_hours[ $key ][ 'to' ];
				if( $open == 0 ){
					$atSundayItWillClose = $close;
				}
				$weekday = $weekdays[ floor( $open / 2400 ) ];
				while( $open >= 2400 ) {
					$open -= 2400;
					$close -= 2400;
				}
				if( $weekday == 'sun' && $close == 2400 ){
					$close = $atSundayItWillClose;
				}
				if( !$hours[ $weekday ] ){
					$hours[ $weekday ] = [];        
				}
				$hours[ $weekday ][] = array( Cana_Util::format_time( $open ), Cana_Util::format_time( $close ) );        
			}
			return $hours;
		}


		// Convert all we have to regular hours again -- 234 will became 2014-01-06 02:24
		$hours = [];
		$atSundayItWillClose = 0;
		foreach( $_hours as $key => $val ){
			$to_days = 0;
			$from = $_hours[ $key ][ 'from' ];
			$to = $_hours[ $key ][ 'to' ];
			$status = $_hours[ $key ][ 'status' ];
			$notes = $_hours[ $key ][ 'notes' ];
			if( $from == 0 && $status == 'open' ){
				$atSundayItWillClose = $to;
			}
			$weekday = $weekdays[ floor( $from / 2400 ) ];
			while( $from >= 2400 ) {
				$to_days--;
				$from -= 2400;
			}
			while( $to >= 2400 ) {
				$to_days++;
				$to -= 2400;
			}
			if( $weekday == 'sun' && $to == 2400 ){
				$to = $atSundayItWillClose;
			}

			$data = array( 'from' => Cana_Util::format_time( $from ), 'to' => Cana_Util::format_time( $to ), 'status' => $status, 'to_days' => $to_days );	
			if( $notes ){
				$data[ 'notes' ] = $notes;
			}
			if( !$hours[ $weekday ] ){
				$hours[ $weekday ] = [];	
			}
			$hours[ $weekday ][] = $data;
		}

		return $hours;
	}

	// Convert hours to PM/AM
	public function formatTime( $time ){
		$time = explode( ':', $time );
		$hour = intval( $time[ 0 ] );
		$minute = intval( $time[ 1 ] );
		$ampm = '';
		switch ( true ) {
			case ( $hour == 0 || $hour == 24 ):
				$hour = 12;
				$ampm = 'am';
				break;
			case ( $hour == 12 ):
				$ampm = 'pm';
				break;
			case ( $hour < 12 ):
				$hour = $hour;
				$ampm = 'am';
				break;
			case ( $hour > 12 ):
				$hour = ( $hour - 12 );
				$ampm = 'pm';
				break;
		}
		return $hour . ( ( $minute > 0 ) ? ':' . str_pad( $minute, 2, '0', STR_PAD_LEFT ) : '' ) . $ampm;
	}

	public function restaurantClosedMessage( $restaurant ){
		
		$_hours = Hour::getRestaurantRegularPlusHolidayHours( $restaurant );

		// Remove the closes status
		foreach ( $_hours as $day => $hours ) {
			foreach( $hours as $key => $hour ){
				if( $_hours[ $day ][ $key ][ 'status' ] != 'open' ){
					unset( $_hours[ $day ][ $key ] );
				}
			}
			// re-index the array
			$_hours[ $day ] = array_values( $_hours[ $day ] );
		}	
		// remove the days without hours
		foreach ( $_hours as $day => $hours ) {
			if( count( $hours ) == 0 ){
				unset( $_hours[ $day ]);
			}
		}

		// Combine the hours/days that closes after midgnith
		$weekdays = [ 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun' ];
		foreach( $weekdays as $day ){
			if( $_hours[ $day ] ){
				$index = array_search( $day, $weekdays );
				// Get the prev day to compare
				if( $index == 0 ){
					$index_prev = count( $weekdays ) - 1;
				} else if( $index == ( count( $weekdays ) - 1 ) ){
					$index_prev = 0;	
				} else {
					$index_prev--;
				}
				$prev_day = $weekdays[ $index_prev ];
				// the current day
				if( $_hours[ $day ] ){
					// If this days starts at midnight that is a chance this hours belongs to prev day
					if( $_hours[ $day ] && $_hours[ $day ][ 0 ] && $_hours[ $day ][ 0 ][ 'from' ] && $_hours[ $day ][ 0 ][ 'from' ] == '0:00' ){
						if( $_hours[ $prev_day ] && $_hours[ $prev_day ][ count( $_hours[ $prev_day ] ) - 1 ] && $_hours[ $prev_day ][ count( $_hours[ $prev_day ] ) - 1 ][ 'from' ] && $_hours[ $prev_day ][ count( $_hours[ $prev_day ] ) - 1 ][ 'to' ] == '0:00' ){
							$_hours[ $prev_day ][ count( $_hours[ $prev_day ] ) - 1 ] = array( 'from' => $_hours[ $prev_day ][ count( $_hours[ $prev_day ] ) - 1 ][ 'from' ], 'to' => $_hours[ $day ][ 0 ][ 'to' ] );
							unset( $_hours[ $day ][ 0 ] );
							$_hours[ $day ] = array_values( $_hours[ $day ] );
						}
					}
				}
			}
		}

		// Convert the hours to format am/pm and merge the segments
		$_partial = [];
		foreach ( $_hours as $day => $hours ) {
			$segments = [];
			foreach( $hours as $key => $hour ){
				$from = Hour::formatTime( $_hours[ $day ][ $key ][ 'from' ] );
				$to = Hour::formatTime( $_hours[ $day ][ $key ][ 'to' ] );
				$segments[] = $from . ' - ' . $to;
			}
			$_partial[ $day ] = join( ', ', $segments );
		}	

		$_group = [];
		// Group the days with the same hour
		foreach( $_partial as $day => $hours ){
			if( !$_group[ $hours ] ){
				$_group[ $hours ] = [];
			}
			$_group[ $hours ][] = $day;
		}

		// Group the days e.g. 'Mon, Tue, Wed, Sat' will became 'Mon - Wed, Sat'
		foreach( $_group as $hours => $days ){
			if( count( $days ) <= 1 ){
				$_group[ $hours ] = ucfirst( join( ', ', $days ) );
				continue;
			}

			$_sequence_index = 0;
			$_in_sequences = [];
			$_no_sequences = [];
			$nextShouldBe = false;
			for( $i = 0; $i < count( $days ); $i++ ){
				$index = array_search( $days[ $i ], $weekdays );

				if( $nextShouldBe !== false ){
					if( $nextShouldBe == $index ){
						$_in_sequences[ $_sequence_index ][ $days[ $i -1 ] ] = array_search( $days[ $i -1 ], $weekdays );
						$_in_sequences[ $_sequence_index ][ $days[ $i ] ] = array_search( $days[ $i ], $weekdays );
						unset( $_no_sequences[ $days[ $i -1 ] ] );
						unset( $_no_sequences[ $days[ $i ] ] );
					} else {
						$_no_sequences[ $days[ $i ] ] = array_search( $days[ $i ], $weekdays );
						$_sequence_index++;
					}
				} else {
					$_no_sequences[ $days[ $i ] ] = array_search( $days[ $i ], $weekdays );	
				}

				$nextShouldBe = ( ( $index + 1 ) < ( count( $weekdays ) ) ) ? ( $index + 1 ) : 0; 
			}
			$_sequences = [];
			foreach ( $_in_sequences as $key => $value ) {
				if( count( $_in_sequences[ $key ] ) == 2 ){
					$separator = ', ';
				} else {
					$separator = ' - ';
				}
				$data = [];
				$keys = array_keys( $_in_sequences[ $key ] );
				$_sequences[ array_shift( $_in_sequences[ $key ] ) ] = ucfirst( array_shift( $keys ) ) . $separator . ucfirst( array_pop( $keys ) );
			}	
			foreach ( $_no_sequences as $key => $value ) {
				$_sequences[ $_no_sequences[ $key ] ] = ucfirst( $key );
			}	
			ksort( $_sequences );
			$_group[ $hours ] = join( ', ', $_sequences );
		}

		$_organized = [];
		// Organize the messy
		foreach( $_group as $hours => $days ){
			if( trim( $hours ) != '' ){
				$_organized[] = $days . ': ' . $hours;	
			}
		}
		return join( ' <br/> ', $_organized );
	}

}