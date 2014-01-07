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

	public function getUTCByRestaurant( $restaurant ){
		$hours = Hour::getWeekUTCByRestaurant( $restaurant, true );
		echo '<pre>'; var_dump( $hours ); exit;

	}

	public function getWeekUTCByRestaurant( $restaurant, $just24hours = false ){

		$minutesInAHour = 24 * 60;

		$hours = $restaurant->hours();
		$_hours = [];
		foreach ( $hours as $hour ) {
			$_hours[ $hour->day ][] = [ $hour->time_open, $hour->time_close ];
		}

		// Get the hollidays and override 
		$_hours = $this->overrideHours( $_hours );
		
		$hours = $_hours;

		if( count( $hours ) == 0 ){
			return $hours;
		}

		// Continue where
		// http://localhost/api/restaurant/hours/1
		// ----> check the date of 00 mondays and 24 sunday
		

		$today = new DateTime( 'now', new DateTimeZone( 'GMT' ) );
		$last_close = false;
		$_hours_utc = [];

		foreach( $hours as $day => $segments ){
			
			foreach( $segments as $times ){

				$open = new DateTime( 'next ' . $day . ' ' . $times[ 0 ], new DateTimeZone( $this->timezone ) );
				$open->setTimezone( new DateTimeZone( 'GMT' ) );

				$interval = $open->diff( $today );
				$interval_open = ( $interval->m * 30 * 24 * 60 ) + ( $interval->d * 24 * 60 ) + ( $interval->h * 60 ) + ( $interval->i );

				$back7Days = false;

				if( $interval_open >= ( $minutesInAHour * 6 ) ){
					$open->modify( '-7 days' );
					$interval = $open->diff( $today );
					$interval_open = ( $interval->m * 30 * 24 * 60 ) + ( $interval->d * 24 * 60 ) + ( $interval->h * 60 ) + ( $interval->i );
					$back7Days = true;
				}

				$close = new DateTime( 'next ' . $day . ' ' . $times[ 1 ], new DateTimeZone( $this->timezone ) );
				$close->setTimezone( new DateTimeZone( 'GMT' ) );

				$interval = $close->diff( $today );
				$interval_close = ( $interval->m * 30 * 24 * 60 ) + ( $interval->d * 24 * 60 ) + ( $interval->h * 60 ) + ( $interval->i );

				if( $back7Days && $interval_close >= ( $minutesInAHour * 6 ) ){
					$close->modify( '-7 days' );
					$interval = $close->diff( $today );
					$interval_close = ( $interval->m * 30 * 24 * 60 ) + ( $interval->d * 24 * 60 ) + ( $interval->h * 60 ) + ( $interval->i );
				}

				$open_ini = $open->format( 'Y-m-d H:i' );
				$open_end = $close->format( 'Y-m-d H:i' );

				// if last close is false and it opens after midnight 
				if( !$last_close && $open->format( 'Hi' ) > '0000' ){
					$last_close = date( 'Y-m-d H:i', strtotime( '-1 minutes', strtotime( $open->format( 'Y-m-d 00:00' ) ) ) );
				}

				if( $last_close ){
					$close_ini = $datetime_from = date( 'Y-m-d H:i', strtotime( '+1 minutes', strtotime( $last_close ) ) );
					$close_end = $datetime_from = date( 'Y-m-d H:i', strtotime( '-1 minutes', strtotime( $open_ini ) ) );
					$_hours_utc[] = ( object ) array( 'from' => $close_ini, 'to' => $close_end, 'status' => 'closed' );
				}

				$last_close = $open_end;

				// if( $interval_open <= $minutesInAHour || $interval_close <= $minutesInAHour ){
					$_hours_utc[] = ( object ) array( 'minutesInAHour' => $minutesInAHour, 'from' => $open_ini, 'interval_open' => $interval_open, 'to' => $open_end, 'interval_close' => $interval_close, 'status' => 'open' );	
				// }

			}
		}
		echo '<pre>'; var_dump( $_hours_utc ); exit;
		return $_hours_utc;
	}

	public function overrideHours( $hours ){

		if( count( $hours ) == 0 ){
			return $hours;
		}

		$weekdays = [ 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun' ];
		
		// Convert to hours starting at monday
		$hoursStartFinish = [];
		foreach( $hours as $day => $segments ){
			$dayshours = array_search( $day, $weekdays ) * 2400;
			foreach( $segments as $times ){
				preg_match( '/(\d+):(\d+)/', $times[ 0 ], $hour_open );
				preg_match( '/(\d+):(\d+)/', $times[ 1 ], $hour_close );
				$hour_open = ( $dayshours + intval( $hour_open[ 1 ] ) * 100 ) + intval( $hour_open[ 2 ] );
				$hour_close = ( $dayshours + intval( $hour_close[ 1 ] ) * 100 ) + intval( $hour_close[ 2 ] );
				$hoursStartFinish[] = [ 'open' => $hour_open, 'close' => $hour_close ];
			}
		}

		$monday = date( 'Y-m-d', strtotime( 'monday this week' ) );
		$sunday = date( 'Y-m-d', strtotime( 'sunday this week' ) );
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
					$hoursStartFinishOverrideClose[] = [ 'start' => $hour_open, 'end' => $hour_close, '_start' => $date_start->format( 'Y-m-d H:i:s' ), '_data_end' => $date_end->format( 'Y-m-d H:i:s' ) ];	
				} else if( $override->type == 'open' ){
					// Merge the override open hours at the open/close array
					$hoursStartFinish[] = [ 'open' => $hour_open, 'close' => $hour_close ];
				}
			}
		}

		$hoursStartFinish = Cana_Util::sort_col( $hoursStartFinish, 'open' );

		// Merge the hours
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

		$_hours = [];
		$atSundayItWillClose = 0;
		foreach( $hoursStartFinish as $key => $val ){
			$open = $hoursStartFinish[ $key ][ 'open' ];
			$close = $hoursStartFinish[ $key ][ 'close' ];
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
			$_hours[ $weekday ][] = array( Cana_Util::format_time( $open ), Cana_Util::format_time( $close ) );	
		}
		return $_hours;
	}

}