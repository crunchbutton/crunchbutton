<?php

class Crunchbutton_Hour extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('hour')
			->idVar('id_hour')
			->load($id);
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

	public function getByRestaurantToExport( $restaurant, $utc = true, $next24hours = false ){

		$hours = $restaurant->hours();

		$_hours = [];

		// Convert the hours to a simple array
		foreach ( $hours as $hour ) {
			$_hours[ $hour->day ][] = [ $hour->time_open, $hour->time_close ];
		}

		// Merge the restaurant hours with the holidays
		$hours = Hour::mergeHolidays( $_hours );

		if( count( $hours ) == 0 ){
			return $hours;
		}

		$getDay = new DateTime( 'now', new DateTimeZone( ( $utc ? 'GMT' : $restaurant->timezone ) ) );
			
		// step back two days
		$getDay->modify( '-2 day' );

		// loop to get all the days of the week, starting by yestarday
		for( $i=0; $i<=6; $i++ ){

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

	// This method merge restaurant hours with the holidays
	public function mergeHolidays( $hours ){

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
																															WHERE id_restaurant = {$this->id_restaurant} 
																															AND (
																															( DATE_FORMAT( date_start, '%Y-%m-%d' ) >= '{$monday}' AND DATE_FORMAT( date_start, '%Y-%m-%d' ) <= '{$sunday}' )
																															OR 
																															( DATE_FORMAT( date_start, '%Y-%m-%d' ) < '{$monday}' AND DATE_FORMAT( date_end, '%Y-%m-%d' ) > '{$monday}' )	) " );
		$hoursStartFinishOverrideClose = [];
		if( $overrides->count() ){

			foreach( $overrides as $override ){

				$monday = new DateTime( date( 'Y-m-d H:i:s', strtotime( 'monday this week' ) ), new DateTimeZone( $this->timezone ) );
				$date_start = new DateTime( $override->date_start, new DateTimeZone( $this->timezone ) );
				
				// Limit the override to this week
				if( $date_start < $monday ){
					$date_start = $monday;
				}
				$dayshours = array_search( strtolower( $date_start->format( 'D' ) ), $weekdays ) * 2400;
				$hour_open = ( $dayshours + intval( $date_start->format( 'H' ) ) * 100 ) + intval( $date_start->format( 'i' ) );

				$sunday = new DateTime( date('Y-m-d H:i:s', strtotime('sunday this week') ), new DateTimeZone( $this->timezone ) );
				$sunday->setTime( 23, 59 );
				$date_end = new DateTime( $override->date_end, new DateTimeZone( $this->timezone ) );
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

		// this foreach will merge the regular hours with the override ones
		foreach( $hoursStartFinishOverrideClose as $keyClose => $valClose ){
			foreach( $hoursStartFinish as $keyOpen => $valOpen ){
				// the close override is between a segment
				if(	$hoursStartFinishOverrideClose[ $keyClose ][ 'start' ] >= $hoursStartFinish[ $keyOpen ][ 'open' ] 
						&& $hoursStartFinishOverrideClose[ $keyClose ][ 'end' ] < $hoursStartFinish[ $keyOpen ][ 'close' ] ){
					$hoursStartFinish[] = [ 'open' => $hoursStartFinishOverrideClose[ $keyClose ][ 'end' ], 'close' => $hoursStartFinish[ $keyOpen ][ 'close' ] ];	
					$hoursStartFinish[ $keyOpen ][ 'close' ] = $hoursStartFinishOverrideClose[ $keyClose ][ 'start' ];
				} else
				// the close override starts after the segment but ends before it
				if(	$hoursStartFinishOverrideClose[ $keyClose ][ 'start' ] <= $hoursStartFinish[ $keyOpen ][ 'open' ] 
						&& $hoursStartFinishOverrideClose[ $keyClose ][ 'end' ] > $hoursStartFinish[ $keyOpen ][ 'open' ] 
						&& $hoursStartFinishOverrideClose[ $keyClose ][ 'end' ] > $hoursStartFinish[ $keyOpen ][ 'close' ] ){
					unset( $hoursStartFinish[ $keyOpen ] );
				} else 
				// the close override starts before the regular hour and ends after it
				if(	$hoursStartFinishOverrideClose[ $keyClose ][ 'start' ] <= $hoursStartFinish[ $keyOpen ][ 'open' ] 
						&& $hoursStartFinishOverrideClose[ $keyClose ][ 'end' ] > $hoursStartFinish[ $keyOpen ][ 'open' ] ){
					$hoursStartFinish[ $keyOpen ][ 'open' ] = $hoursStartFinishOverrideClose[ $keyClose ][ 'end' ];
				} else 
				// the close override starts after the regular hour and ends after it
				if(	$hoursStartFinishOverrideClose[ $keyClose ][ 'start' ] >= $hoursStartFinish[ $keyOpen ][ 'open' ] 
						&& $hoursStartFinishOverrideClose[ $keyClose ][ 'start' ] < $hoursStartFinish[ $keyOpen ][ 'close' ]
						&& $hoursStartFinishOverrideClose[ $keyClose ][ 'end' ] > $hoursStartFinish[ $keyOpen ][ 'close' ] ){
					$hoursStartFinish[ $keyOpen ][ 'close' ] = $hoursStartFinishOverrideClose[ $keyClose ][ 'start' ];
				}
			}
		}

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
					if( $_hours[ $key ][ 'from' ] >= $closedHour[ 'start' ] && $_hours[ $key ][ 'to' ] <= $closedHour[ 'end' ] ){
						$_hours[ $key ][ 'notes' ] = $closedHour[ 'notes' ];
					}
				}
			}
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

}