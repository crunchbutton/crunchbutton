<?php

class Crunchbutton_Hour extends Cana_Table_Trackchange {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('hour')
			->idVar('id_hour')
			->load($id);
	}

	public static function minutesBuffer(){
		return 30;
	}

	public static function restaurantNextCloseTime( $restaurant ){
		$today = new DateTime( 'now', new DateTimeZone( $restaurant->timezone ) );
		$day = strtolower( $today->format( 'D' ) );
		$hours = self::getByRestaurantWeek( $restaurant, false );
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

	public static function restaurantNextOpenTimeMessage( $restaurant, $utc = false ){
		$nexOpen = self::restaurantNextOpenTime( $restaurant, $utc = false );
		$day = new DateTime( 'now', new DateTimeZone( ( $utc ? $utc : $restaurant->timezone ) ) );

		$tomorrow = new DateTime( $day->format( 'Y-m-d' ) . '23:59:59', new DateTimeZone( ( $utc ? $utc : $restaurant->timezone ) ) );
		$isToday = ( $nexOpen->format( 'YmdHis' ) < $tomorrow->format( 'YmdHis' ) );
		$tomorrow->modify( '+ 1 day' );
		$isTomorrow = ( !$isToday && $nexOpen->format( 'YmdHis' ) < $tomorrow->format( 'YmdHis' ) );

		$message = 'Available at ' . $nexOpen->format( 'g' );
		if( $nexOpen->format( 'i' ) != '00' ){
			$message .= ':' . $nexOpen->format( 'i' );
		}
		$message .= $nexOpen->format( 'A' );
		if( $isToday ){
			$message .= ' Today';
		} else if( $isTomorrow ){
			$message .= ' Tomorrow';
		} else {
			$message .= ' on ';
			$message .= $nexOpen->format( 'D' );
		}
		$message .= '!';

		$result = array(	'day' => $nexOpen->format( 'l' ),
											'hour' => intval( $nexOpen->format( 'h' ) ),
											'min' => $nexOpen->format( 'i' ),
											'today' => ( $isToday ? 'Today' : false ),
											'tomorrow' => ( $isTomorrow ? 'Tomorrow' : false ),
											'ampm' => strtoupper( $nexOpen->format( 'a' ) ),
											'message' => $message );
		return $result;
	}

	public static function restaurantNextOpenTime( $restaurant, $utc = false ){
		$today = new DateTime( 'now', new DateTimeZone( $restaurant->timezone ) );
		$day = strtolower( $today->format( 'D' ) );
		$hours = self::getByRestaurantWeek( $restaurant, false );
		if (!$hours) {
			return false;
		}
		foreach( $hours as $hour ){
			if( $hour->status == 'open' ){
				$open = new DateTime( $hour->from, new DateTimeZone( $restaurant->timezone ) );
				if( $open >= $today ){
					if( $utc ){
						$open->setTimezone( new DateTimeZone( 'UTC' ) );
					}
					return $open;
				}
			}
		}
		return false;
	}

	public static function restaurantClosesIn( $restaurant ){
		$today = new DateTime( 'now', new DateTimeZone( $restaurant->timezone ) );
		$close = self::restaurantNextCloseTime( $restaurant );
		if( $close ){
			$interval = $today->diff( $close );
			$minutes = ( $interval->m * 30 * 24 * 60 ) + ( $interval->d * 24 * 60 ) + ( $interval->h * 60 ) + ( $interval->i );
			if( $minutes > 0 ){
				return $minutes;
			}
		}
		return false;
	}

	public static function restaurantOpensIn( $restaurant ){
		$today = new DateTime( 'now', new DateTimeZone( $restaurant->timezone ) );
		$open = self::restaurantNextOpenTime( $restaurant );
		if( $open ){
			$interval = $today->diff( $open );
			$minutes = ( $interval->m * 30 * 24 * 60 ) + ( $interval->d * 24 * 60 ) + ( $interval->h * 60 ) + ( $interval->i );
			if( $minutes > 0 ){
				return $minutes;
			}
		}
		return false;
	}

	public static function restaurantIsOpen( $restaurant, $dt = null ){

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

				$time_open = explode( ':' , $hour->time_open );
				$hr = $time_open[ 0 ] ? $time_open[ 0 ] : '0';
				$min = substr( $time_open[ 1 ], 0, 2 );
				$min = $min ? $min : '00';
				$hour->time_open =  $hr . ':' . $min;

				$time_close = explode( ':' , $hour->time_close );
				$hr = $time_close[ 0 ] ? $time_close[ 0 ] : '0';
				$min = substr( $time_close[ 1 ], 0, 2 );
				$min = $min ? $min : '00';
				$hour->time_close =  $hr . ':' . $min;

				$open  = new DateTime( 'today ' . $hour->time_open,  new DateTimeZone( $restaurant->timezone ) );
				$close = new DateTime( 'today ' . $hour->time_close, new DateTimeZone( $restaurant->timezone ) );
			}

			// if closeTime before openTime, then closeTime should be for tomorrow
			if ( $close->getTimestamp() < $open->getTimestamp() ) {
				date_add( $close, date_interval_create_from_date_string( '1 day' ) );
			}

			if ( $today->getTimestamp() >= $open->getTimestamp() && $today->getTimestamp() <= $close->getTimestamp() ) {
				$community = $restaurant->community();
				if( $restaurant->delivery_service && $community->combine_restaurant_driver_hours ){
					$drivers = $community->activeDrivers()->get( 0 );
					if( $drivers > 0 ){
						return true;
					}
				} else {
					return true;
				}
			}
		}

		return false;
	}

	public static function hoursOpenedByRestaurantWeekDay( $restaurant, $day ){

		$hours_opened = [];

		$hours = self::getByRestaurantWeek( $restaurant, false );
		$day = new DateTime( $day, new DateTimeZone( $restaurant->timezone ) );

		foreach ( $hours as $hour ) {
			if( $hour->status == 'open' ){
				$from = explode( ' ' , $hour->from );
				$to = explode( ' ' , $hour->to );
				$from_day = $from[ 0 ];
				$to_day = $to[ 0 ];
				if( $from_day == $day->format( 'Y-m-d' ) ){
					$from_hour = intval( explode( ':', $from[ 1 ] )[0] );
					$to_hour = intval( explode( ':', $to[ 1 ] )[0] );
					if( $to_hour < $from_hour ){
						$to_hour = 23;
					}
					for( $i = $from_hour; $i <= $to_hour; $i++ ){
						$hours_opened[ $i ]	= true;
					}
				}
				if( $to_day == $day->format( 'Y-m-d' ) && $from_day != $day->format( 'Y-m-d' ) ){
					$from_hour = 0;
					$to_hour = intval( explode( ':', $to[ 1 ] )[0] );
					for( $i = $from_hour; $i <= $to_hour; $i++ ){
						$hours_opened[ $i ]	= true;
					}
				}
			}
		}
		return $hours_opened;
	}

	public static function firstHourNextDay( $hours, $current ){
		$weekdays = [ 'mon' => 0, 'tue' => 1, 'wed' => 2, 'thu' => 3, 'fri' => 4, 'sat' => 5, 'sun' => 6 ];
		$next_key = 0;
		foreach( $weekdays as $key => $val ){
			if( $current == $key ){
				$next_key = $val + 1;
				if( $next_key == 7 ){
					$next_key = 0;
				}
			}
		}
		$next_week = null;
		foreach( $weekdays as $key => $val ){
			if( $val == $next_key ){
				$next_week = $key;
			}
		}

		$next_hours = false;
		$_hour = null;
		foreach( $hours as $hour ){
			if( trim( $hour->day ) == trim( $next_week ) ){
				$_hours_to_int = intval( str_replace( ':' , '', $hour->time_open ) );
				if( $next_hours === false || $next_hours !== false && $next_hours > $_hours_to_int ){
					$next_hours = $_hours_to_int;
					$_hour = $hour;
				}
			}
		}
		return $_hour;
	}

	public static function hoursByRestaurant( $restaurant, $gmt = false, $justAssignedShiftHours = false ){

		$hours = self::q( "SELECT * FROM hour WHERE id_restaurant = {$restaurant->id_restaurant}" );
		if ( $gmt ) {
			$timezone = new DateTime( 'now ', new DateTimeZone( $restaurant->timezone ) );
			$timezone = $timezone->format( 'O' );
			foreach ( $hours as $hour ) {
				$open = new DateTime( 'next '.$hour->day. ' ' .$hour->time_open, new DateTimeZone( $restaurant->timezone ) );
				$open->setTimezone( new DateTimeZone( 'UTC' ) );
				$close = new DateTime( 'next '.$hour->day. ' ' .$hour->time_close, new DateTimeZone( $restaurant->timezone ) );
				$close->setTimezone( new DateTimeZone( 'UTC' ) );
				$hour->time_open = $open->format( 'Y-m-d H:i' );
				$hour->time_close = $close->format( 'Y-m-d H:i' );
			}
		}
		$restaurant->_hours_ = $hours;
		if( Crunchbutton_Util::isCockpit() && !Crunchbutton_Util::isCLI() && !$restaurant->force_buffer ){
			return $restaurant->_hours_;
		}

		// Add restaurant buffer time for 3rd party delivery restaurants #6332
		if( $restaurant->delivery_service && self::minutesBuffer() ){

			// Convert the hours to a simple array
			$_restaurant_hours = [];
			foreach ( $restaurant->_hours_ as $hour ) {
				if( !isset( $_restaurant_hours[ trim( $hour->day ) ] ) ){
					$_restaurant_hours[ trim( $hour->day ) ] = [];
				}
				$_restaurant_hours[ trim( $hour->day ) ][] = [ trim( $hour->time_open ), trim( $hour->time_close ) ];
			}

			uksort( $_restaurant_hours,
			function( $a, $b ) {
				$weekdays = [ 'mon' => 0, 'tue' => 1, 'wed' => 2, 'thu' => 3, 'fri' => 4, 'sat' => 5, 'sun' => 6 ];
				return( $weekdays[ $a ] > $weekdays[ $b ] );
			} );

			$w_next = [ 'mon' => 'tue', 'tue' => 'wed', 'wed' => 'thu', 'thu' => 'fri', 'fri' => 'sat', 'sat' => 'sun', 'sun' => 'mon' ];

			foreach( $_restaurant_hours as $day => $segments ){
				foreach( $segments as $k1 => $segment ){
					if( $segment[ 1 ] == '24:00' ){
						$next_day = $_restaurant_hours[ $w_next[ $day ] ];
						if( $next_day ){
							foreach( $next_day as $k2 => $next_segment ){
								if( $next_segment[ 0 ] == '0:00' ){
									$_day_hour = explode( ':', $segment[ 1 ] );
									$_next_hour = explode( ':', $next_segment[ 1 ] );
									$_restaurant_hours[ $day ][ $k1 ][ 1 ] = ( $_day_hour[ 0 ] + $_next_hour[ 0 ] ) . ':' . $_next_hour[ 1 ];;
									unset( $_restaurant_hours[ $w_next[ $day ] ][ $k2 ] );
								}
							}
						}
					}
				}
			}

			$_restaurant_hours_objects = [];
			foreach( $_restaurant_hours as $day => $segments ){
				foreach( $segments as $segment ){
					$_hour = (object) [];
					$_hour->day = $day;
					$_hour->time_open = $segment[ 0 ];
					$_hour->time_close = $segment[ 1 ];
					$_restaurant_hours_objects[] = $_hour;
				}
			}

			$community = $restaurant->community();

			// if the restaurant doesn't belongs to a community, just ignore it
			if( $community->id_community ){

				// this flash is needed because this method is called recursivelly
				$_hours_utc_buffered = [];

				// So, if a restaurant closes less than 30 minutes after the shifts close, we want a 30 minute buffer to kick in
				if( $justAssignedShiftHours ){
					$community_hrs = $community->assignedShiftsForNextWeek();
				} else {
					$community_hrs = $community->shiftsForNextWeek( true );
				}
				$community_hrs = $community_hrs->get( 0 );

				// empty array to store the merged hours
				$_community_hours = [];

				// Convert the hours to a simple array
				if( $community_hrs && count( $community_hrs ) ){
					foreach ( $community_hrs as $hour ) {
						if( !isset( $_community_hours[ trim( $hour->day ) ] ) ){
							$_community_hours[ trim( $hour->day ) ] = [];
						}
						$_community_hours[ trim( $hour->day ) ][] = [ trim( $hour->time_open ), trim( $hour->time_close ) ];
					}

					uksort( $_community_hours,
					function( $a, $b ) {
						$weekdays = [ 'mon' => 0, 'tue' => 1, 'wed' => 2, 'thu' => 3, 'fri' => 4, 'sat' => 5, 'sun' => 6 ];
						return( $weekdays[ $a ] > $weekdays[ $b ] );
					} );
				}

				$community_closes = [];
				$community_opens = [];

				foreach ( $_community_hours as $day => $hours ) {
					if( !$community_closes[ $day ] ){
						$community_closes[ $day ] = 0;
					}
					if( !$community_opens[ $day ] ){
						$community_opens[ $day ] = 2400;
					}
					foreach( $hours as $hour ){
						$close_at = intval( str_replace( ':', '', $hour[ 1 ] ) );
						$opens_at = intval( str_replace( ':', '', $hour[ 0 ] ) );
						if( $close_at == 0 ){
							$close_at = 2400;
						}
						$community_closes[ $day ] = ( $community_closes[ $day ] > $close_at ) ? $community_closes[ $day ] : $close_at;
						$community_opens[ $day ] = ( $community_opens[ $day ] < $opens_at ) ? $community_opens[ $day ] : $opens_at;
					}
				}

				$buffer_minutes = self::minutesBuffer();

				$restaurant___hours_ = [];

				foreach ( $_restaurant_hours_objects as $hour ) {
					$restaurant___hours_[] = clone $hour;
				}

				foreach ( $_restaurant_hours_objects as $hour ) {

					if( $hour->buffered ){
						continue;
					}

					if( strtolower( date( 'D' ) ) == $hour->day ){
						$day = date( 'Y-m-d' );
					} else {
						$day = date('Y-m-d', strtotime("next " . $hour->day ) );
					}

					if( $justAssignedShiftHours ){
						$hour->date = $day;
					}

					$close_time = intval( str_replace( ':', '', $hour->time_close ) );
					$opens_time = intval( str_replace( ':', '', $hour->time_open ) );

					// open
					if( $community_opens[ $hour->day ] ){
						$substr = ( strlen( $community_opens[ $hour->day ] ) == 4 ) ? 2 : 1;
						$minutes = ( intval( substr( $community_opens[ $hour->day ], 0, $substr ) ) * 60 ) + substr( $community_opens[ $hour->day ], -2 );
						$substr = ( strlen( $opens_time ) == 4 ) ? 2 : 1;
						$opens_time_minutes = ( intval( substr( $opens_time, 0, $substr ) ) * 60 ) + substr( $opens_time, -2 );

						if( $community_opens[ $hour->day ] > $opens_time ){

							$opens_time = $community_opens[ $hour->day ] . '';
							$_min = substr( $opens_time, -2 );
							if( strlen( $opens_time ) == 4 ){
								$_hour = substr( $opens_time, 0, 2 );
							} else {
								$_hour = substr( $opens_time, 0, 1 );
							}
							$opens_time = $_hour . ':' . $_min;
							$hour->time_open = $opens_time;
						}
						// echo '<pre>';var_dump( $hour );
					}

					// closes
					if( $community_closes[ $hour->day ] ){
						$substr = ( strlen( $community_closes[ $hour->day ] ) == 4 ) ? 2 : 1;
						$minutes = ( intval( substr( $community_closes[ $hour->day ], 0, $substr ) ) * 60 ) + substr( $community_closes[ $hour->day ], -2 );
						$substr = ( strlen( $close_time ) == 4 ) ? 2 : 1;
						$close_time_minutes = ( intval( substr( $close_time, 0, $substr ) ) * 60 ) + substr( $close_time, -2 );

						if( $community_closes[ $hour->day ] < $close_time ){
							$close_time = $community_closes[ $hour->day ] . '';
							$_min = substr( $close_time, -2 );
							if( strlen( $close_time ) == 4 ){
								$_hour = substr( $close_time, 0, 2 );
							} else {
								$_hour = substr( $close_time, 0, 1 );
							}
							$close_time = $_hour . ':' . $_min;
							$hour->time_close = $close_time;
						}
						else { //if( ( $minutes - $buffer_minutes ) <= $close_time_minutes ){

							$add_buffer = false;
							$_close = null;
							if( $close_time_minutes < ( 24 * 60 ) ){
								$add_buffer = true;
							}
							if( $close_time_minutes == $minutes ){
								if( $close_time_minutes == ( 24 * 60 ) ){
									$next_day_hours = self::firstHourNextDay( $restaurant___hours_, $hour->day );
									$int_open = intval( str_replace( ':' , '', $next_day_hours->time_open ) );
									if( $int_open == 0 ){
										$int_close = intval( str_replace( ':' , '', $next_day_hours->time_close ) );
										if( $int_close < $buffer_minutes ){
											$close = new DateTime( $day . ' ' . $next_day_hours->time_close,  new DateTimeZone( 'UTC' ) );
											$close->modify( '- ' . $buffer_minutes . ' minutes' );
											$_close = $close->format( 'H:i' );
										}
									} else {
										$add_buffer = true;
									}
								} else {
									$add_buffer = true;
								}
							}

							$close = new DateTime( $day . ' ' . $hour->time_close,  new DateTimeZone( 'UTC' ) );
							if( $add_buffer ){
								$close->modify( '- ' . $buffer_minutes . ' minutes' );
							}
							$hour->time_close = $close->format( 'H:i' );
							if( $_close ){
								$hour->time_close = $_close;
								$_close = null;
							}
						}
						// echo '<pre>';var_dump( $hour );

					} else {
						// if the community doent have shift remove the ours
						$hour->day = null;
						$hour->time_open = null;
						$hour->time_close = null;
					}

					$_opens_time = intval( str_replace( ':' , '', $opens_time ) );
					$_close_time = intval( str_replace( ':' , '', $close_time ) );

					if( $_opens_time > $_close_time ){
						$hour->day = null;
						$hour->time_open = null;
						$hour->time_close = null;
					}
					$hour->buffered = true;
				}
			}
			// echo '<pre>';var_dump( $_restaurant_hours_objects );exit();
			return $_restaurant_hours_objects;
		}

		return $restaurant->_hours_;
	}

	public static function getByRestaurantWeek( $restaurant, $utc = true){
		return self::getByRestaurantToExport( $restaurant, $utc);
	}

	public static function getByRestaurantNext24Hours( $restaurant, $utc = true, $sd = null){
		return self::getByRestaurantToExport( $restaurant, $utc, true, $sd);
	}

	public static function getRestaurantRegularPlusHolidayHours( $restaurant ){

		Crunchbutton_Config::getVal( 'auto_close_use_community_hours' );

		// If the restaurant is 3rd party delivery and the community is auto close
		// due to it has no driver get the community shift hours
		// added a config key if we need to disable it on live
		$hours = $restaurant->hours();

		// empty array to store the merged hours
		$_hours = [];

		// Convert the hours to a simple array
		if( $hours && count( $hours ) ){
			foreach ( $hours as $hour ) {
				if( !isset( $_hours[ trim( $hour->day ) ] ) ){
					$_hours[ trim( $hour->day ) ] = [];
				}
				$_hours[ trim( $hour->day ) ][] = [ trim( $hour->time_open ), trim( $hour->time_close ) ];
			}
		}

		uksort( $_hours,
			function( $a, $b ) {
				$weekdays = [ 'mon' => 0, 'tue' => 1, 'wed' => 2, 'thu' => 3, 'fri' => 4, 'sat' => 5, 'sun' => 6 ];
				return( $weekdays[ $a ] > $weekdays[ $b ] );
		} );

		$community = $restaurant->community();

		// Fix Restaurant Hours Displayed #5920: https://github.com/crunchbutton/crunchbutton/issues/5920#issuecomment-119308322
		if( $restaurant->delivery_service && $community->combine_restaurant_driver_hours ){

			// community hours with driver
			$community_hrs = $restaurant->assignedShiftHours( true );

			// empty array to store the merged hours
			$_community_hours = [];

			// Convert the hours to a simple array
			if( $community_hrs && count( $community_hrs ) ){
				foreach ( $community_hrs as $hour ) {
					if( !isset( $_community_hours[ trim( $hour->day ) ] ) ){
						$_community_hours[ trim( $hour->day ) ] = [];
					}
					$_community_hours[ trim( $hour->day ) ][] = [ trim( $hour->time_open ), trim( $hour->time_close ) ];
				}

				uksort( $_community_hours,
				function( $a, $b ) {
					$weekdays = [ 'mon' => 0, 'tue' => 1, 'wed' => 2, 'thu' => 3, 'fri' => 4, 'sat' => 5, 'sun' => 6 ];
					return( $weekdays[ $a ] > $weekdays[ $b ] );
				} );

				foreach( $_community_hours as $day => $hours ){

					// just merge to the current day
					$open = null;
					$close = null;

					$_segments_day = [];

					foreach( $hours as $hour ){

						$community_segment = $hour;

						$community_open = intval( str_replace( ':' , '', $community_segment[ 0 ] ) );
						$community_close = intval( str_replace( ':' , '', $community_segment[ 1 ] ) );
						if( $community_close == 0 && $community_segment[ 1 ] == '00:00' ){
							$community_close = 2400;
						}

						$restaurant_segments = $_hours[ $day ];
						if( $restaurant_segments ){

							foreach( $restaurant_segments as $restaurant_segment ){

								$restaurant_open = intval( str_replace( ':' , '', $restaurant_segment[ 0 ] ) );
								$restaurant_close = intval( str_replace( ':' , '', $restaurant_segment[ 1 ] ) );

								if( $community_open > $restaurant_open ){
									$open = $community_segment[ 0 ];
								} else if( $community_open <= $restaurant_open ){
									$open = $restaurant_segment[ 0 ];
								}

								if( $community_close < $restaurant_close ){
									$close = $community_segment[ 1 ];
								} else if( $community_close >= $restaurant_close ){
									$close = $restaurant_segment[ 1 ];
								}

								if( $open && $close && intval( str_replace( ':' , '', $open ) ) < intval( str_replace( ':' , '', $close ) ) ){
									$_segments_day[] = [ $open, $close ];
								}
							}
						}
					}
					$_hours[ $day ] = $_segments_day;
				}
			}
		}
		// Merge the restaurant hours with the holidays
		return self::mergeHolidays( $_hours, $restaurant );
	}

	public static function getByRestaurantToExport( $restaurant, $utc = true, $next24hours = false , $sd = null){

		$hours = self::getRestaurantRegularPlusHolidayHours( $restaurant );

		if( count( $hours ) == 0 ){
			return $hours;
		}

		$_hours = [];

		// fix hours
		foreach( $hours as $day => $segments ){
			if( !$_hours[ $day ] ){
				$_hours[ $day ] = [];
			}
			$next_is_zero = false;
			foreach( $segments as $segment ){
				$_from = intval( str_replace( ':' , '', $segment[ 'from' ] ) );
				$_to = intval( str_replace( ':' , '', $segment[ 'to' ] ) );
				$save = false;
				if( $_from != $_to ){
					$save = true;
				}
				if( $_from > $_to && $_to == 1200 ){
					$segment[ 'to' ] = '24:00';
					$save = true;
					$next_is_zero = true;
				}
				if( $_from == 1201 && $next_is_zero ){
					$segment[ 'from' ] = '0:01';
					$next_is_zero = false;
				}
				if( $save ){
					$_hours[ $day ][] = $segment;
				}
			}
		}

		$hours = $_hours;

		$sd = is_null($sd) ? new DateTime( 'now', new DateTimeZone( ( $utc ? 'UTC' : $restaurant->timezone ) ) ) : $sd;

		$getDay = clone $sd;

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
					// create a Datetime to the end time
					$end = new DateTime( $getDay->format( 'Y-m-d' ) . ' ' . $times[ 'to' ], new DateTimeZone( $restaurant->timezone ) );

					$diff_before = Util::intervalToSeconds( $start->diff( $end ) );

					// Convert to UTC/UTC case it is needed
					if( $utc ){
						$start->setTimezone( new DateTimeZone( 'UTC' ) );
						$end->setTimezone( new DateTimeZone( 'UTC' ) );
					}

					$diff_after = Util::intervalToSeconds( $start->diff( $end ) );

					// For some odd reason the end hours is not being converted to UTC correctly, this piece of code will make sure that
					// the time diff remains the same after the conversion
					if( $diff_before != $diff_after ){
						$seconds = $diff_before - $diff_after;
						$end->modify( '-' . $seconds . ' seconds' );
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

			$now_plus_24 = clone $sd;
			$now_plus_24->modify( '+1 day' );
			$now_plus_24->modify( '-5 minutes' );
			$now = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
			$now->modify( '-5 minutes' );

			if( $_hours_utc ){

				foreach ( $_hours_utc as $hour ) {

					$data = false;

					$from = new DateTime( $hour->from, new DateTimeZone( ( $utc ? 'UTC' : $restaurant->timezone ) ) );
					$to = new DateTime( $hour->to, new DateTimeZone( ( $utc ? 'UTC' : $restaurant->timezone ) ) );

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
			}
			// return the last 24 hours
			return $_hours;

		} else {
			// Return the whole week
			return $_hours_utc;
		}

	}

	// Legacy method
	public static function hoursStartingMondayUTC( $hours ){

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
				$close->setTimezone(new DateTimeZone('UTC'));
				$open->setTimezone(new DateTimeZone('UTC'));
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

		$_hours = Cana_Util::sort_col( $_hours, 'open' );

		// Merge the regular hours
		foreach( $_hours as $key => $val ){
			$getNext = false;
			foreach( $_hours as $keyNext => $valNext ){
				if( $getNext ){
					if( $_hours[ $keyNext ][ 'open' ] <= $_hours[ $key ][ 'close' ]
							&& $_hours[ $keyNext ][ 'close' ] - $_hours[ $key ][ 'open' ] < 3600 ) {
						$_hours[ $key ][ 'close' ] = $_hours[ $keyNext ][ 'close' ];
						unset( $_hours[ $keyNext ] );
						$getNext = false;
					}
				}
				if( $key == $keyNext ){
					$getNext = true;
				}
			}
		}

		return $_hours;
	}

	// This method merge restaurant hours with the holidays
	public static function mergeHolidays( $hours, $restaurant, $convertHours = true ){

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
																															AND
																															( date_start >= '{$monday} 00:00:00' AND date_start <= '{$sunday} 23:59:59' )
																															OR
																															(  date_start < '{$monday} 00:00:00' AND date_end > '{$monday} 23:59:59' ) " );
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
					$open = Cana_Util::sum_minutes( $force_close_end, 1 );
					if( $open != $hoursStartFinish[ $keyOpen ][ 'close' ] ){
						$hoursStartFinish[ $keyOpen ][ 'open' ] = Cana_Util::sum_minutes( $force_close_end, 1 );
					}
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
					if( ( $_hours[ $key ][ 'from' ] <= $closedHour[ 'start' ] && $_hours[ $key ][ 'to' ] >= $closedHour[ 'end' ] ) ||
						( $_hours[ $key ][ 'from' ] >= $closedHour[ 'start' ] && $_hours[ $key ][ 'to' ] <= $closedHour[ 'end' ] ) ){
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
	public static function formatTime( $time ){
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

	public static function closedMessage( $hours ){

		$weekdays = [ 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun' ];

		$_partial = $hours;

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

	public static function restaurantClosedMessage( $restaurant ){

		$_hours = self::getRestaurantRegularPlusHolidayHours( $restaurant );

		// Remove the closes status
		foreach ( $_hours as $day => $hours ) {
			foreach( $hours as $key => $hour ){
				if( $_hours[ $day ][ $key ][ 'status' ] != 'open' ){
					unset( $_hours[ $day ][ $key ] );
				}
				if( $_hours[ $day ][ $key ][ 'from' ] === $_hours[ $day ][ $key ][ 'to' ] ){
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
				$from = self::formatTime( $_hours[ $day ][ $key ][ 'from' ] );
				$to = self::formatTime( $_hours[ $day ][ $key ][ 'to' ] );
				$segments[] = $from . ' - ' . $to;
			}
			$_partial[ $day ] = join( ', ', $segments );
		}
		return self::closedMessage( $_partial );
	}
}