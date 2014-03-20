<?php

class Crunchbutton_Community_Shift extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('community_shift')
			->idVar('id_community_shift')
			->load($id);
	}

	public function autoCopyLastWeek(){}

	public function shiftsByDay( $date ){
		Crunchbutton_Community_Shift::createRecurringEvent( $date );
		return Crunchbutton_Community_Shift::q( 'SELECT cs.* FROM community_shift cs INNER JOIN community c ON c.id_community = cs.id_community WHERE DATE_FORMAT( cs.date_start, "%Y-%m-%d" ) = "' . $date . '" ORDER BY c.name, cs.date_start ASC' );
	}

	public function shiftsByCommunityPeriod( $id_community, $from, $to ){
		return Crunchbutton_Community_Shift::q( 'SELECT cs.* FROM community_shift cs WHERE DATE_FORMAT( cs.date_start, "%Y-%m-%d" ) >= "' . $from . '" AND DATE_FORMAT( cs.date_end, "%Y-%m-%d" ) <= "' . $to . '" AND id_community = "' . $id_community . '" ORDER BY cs.date_start ASC' );
	}

	public function week(){
		// Start week at monday #2666
		$date = DateTime::createFromFormat( 'Y-m-d H:i:s', $this->dateStart()->format( 'Y-m-d H:i:s' ), new DateTimeZone( $this->timezone() ) );	
		return $date->format( 'W' );
	}

	public function year(){
		// Start week at monday #2666
		$date = DateTime::createFromFormat( 'Y-m-d H:i:s', $this->dateStart()->format( 'Y-m-d H:i:s' ), new DateTimeZone( $this->timezone() ) );	
		return $date->format( 'Y' );
	}

	public function firstDayOfWeek(){
		// Start week at monday #2666
		$year = $this->year();
		$week = $this->week();
		return new DateTime( date( 'Y-m-d', strtotime( $year . 'W' . $week . 1 ) ), new DateTimeZone( c::config()->timezone  ) );
	}

	public function lastDayOfWeek(){
		// Start week at monday #2666
		$year = $this->year();
		$week = $this->week();
		$day = new DateTime( date( 'Y-m-d', strtotime( $year . 'W' . $week . 1 ) ), new DateTimeZone( c::config()->timezone  ) );
		$day->modify( '+ 6 day' );
		return $day;
	}

	public function shiftByCommunityDay( $id_community, $date ){
		Crunchbutton_Community_Shift::createRecurringEvent( $date );
		return Crunchbutton_Community_Shift::q( 'SELECT * FROM community_shift WHERE id_community = "' . $id_community . '" AND DATE_FORMAT( date_start, "%Y-%m-%d" ) = "' . $date . '" AND active = 1 ORDER BY id_community_shift ASC' );
	}

	public function createRecurringEvent( $date ){
		// Search for recurring events
		$day =  new DateTime( $date, new DateTimeZone( c::config()->timezone ) );
		$weekday = $day->format( 'w' );
		$shifts = Crunchbutton_Community_Shift::q( 'SELECT * FROM community_shift WHERE recurring = 1 AND DATE_FORMAT( date_start, "%w" ) = "' . $weekday . '"' );
		// Create the recurring events
		foreach( $shifts as $shift ){
			if( $shift->dateStart()->format( 'Ymd' ) < $day->format( 'Ymd' ) ){
				$date_base = DateTime::createFromFormat( 'Y-m-d H:i:s', $date . ' 00:00:00', new DateTimeZone( c::config()->timezone ) );
				$hours = Crunchbutton_Admin_Hour::segmentToDate( $date_base, $shift->startEndToString(), $timezone );
				if( $hours ){
					// Check if the recurring event was already created
					$checkShift = Crunchbutton_Community_Shift::q( 'SELECT * FROM community_shift WHERE id_community_shift_father = ' . $shift->id_community_shift . ' AND date_start = "' . $hours[ 'start' ] . '" AND date_end = "' . $hours[ 'end' ] . '"' );
					if( !$checkShift->id_community_shift ){
						$newShift = new Crunchbutton_Community_Shift();
						$newShift->id_community = $shift->id_community;
						$newShift->date_start = $hours[ 'start' ];
						$newShift->date_end = $hours[ 'end' ];
						$newShift->active = 1;
						$newShift->id_community_shift_father = $shift->id_community_shift;
						if( $newShift->date_start && $newShift->date_end ){
							$newShift->save();	
						}
					}
				}
			}
		}
	}


	public function shiftByCommunity( $id_community ){
		$weekdays = [ 'mon' =>  false, 'tue' =>  false, 'wed' =>  false, 'thu' =>  false, 'fri' =>  false, 'sat' =>  false, 'sun'  =>  false ];
		foreach( $weekdays as $day => $val ){
			$shifts = Crunchbutton_Community_Shift::q( 'SELECT * FROM community_shift WHERE id_community = "' . $id_community . '" AND day = "' . $day . '" ORDER BY id_community_shift ASC' );	
			$segment = [];
			foreach ( $shifts as $shift ) {
				$segment[] = Crunchbutton_Community_Shift::startEndToSegment( $shift->start, $shift->end );
			}
			$weekdays[ $day ] = join( ', ', $segment );
		}
		return $weekdays;
	}

	public function parseSegment( $segment ){
		$pattern = '@^ *(\d+)(?:\:(\d+))? *(am|pm) *(?:to|-) *(\d+)(?:\:(\d+))? *(am|pm) *$@i';
		preg_match( $pattern, $segment , $matches);
		$start = Crunchbutton_Community_Shift::parseHour( $matches[ 1 ], $matches[ 2 ], $matches[ 3 ] );
		$end = Crunchbutton_Community_Shift::parseHour( $matches[ 4 ], $matches[ 5 ], $matches[ 6 ] );
		return array( 'start' => $start, 'end' => $end );
	}

	public function community(){
		if( !$this->_community ){
			$this->_community = Crunchbutton_Community::o( $this->id_community );
		}
		return $this->_community;
	}

	public function removeRecurring( $id_community_shift ){
		$shift = Crunchbutton_Community_Shift::o( $id_community_shift );
		if( $shift->id_community_shift_father ){
			c::db()->query( "UPDATE community_shift SET recurring = 0 WHERE id_community_shift = " . $shift->id_community_shift_father );	
			Crunchbutton_Community_Shift::removeRecurringChildren( $shift->id_community_shift_father );
		}		
	}

	public function removeRecurringChildren( $id_community_shift_father ){
		$now =  new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		c::db()->query( 'DELETE FROM community_shift WHERE id_community_shift_father = ' . $id_community_shift_father . ' AND date_start >= "' . $now->format( 'Y-m-d' ) . '"' );
	}

	public function remove( $id_community_shift ){
		$shift = Crunchbutton_Community_Shift::o( $id_community_shift );
		if( $shift->recurring ){
			// If it is a recurring remove its childen
			Crunchbutton_Community_Shift::removeRecurringChildren( $shift->id_community_shift );
		}
		// If it has a father, just desactive the event - to avoid it the be re created again
		if( $shift->id_community_shift_father ){
			c::db()->query( "UPDATE community_shift SET active = 0 WHERE id_community_shift = " . $id_community_shift );
		} else {
			c::db()->query( "DELETE FROM community_shift WHERE id_community_shift = " . $id_community_shift );
		}		
	}

	public function removeHoursFromDay( $id_community, $date ){
		return c::db()->query( "DELETE FROM community_shift WHERE id_community = $id_community AND DATE_FORMAT( date_start, '%Y-%m-%d' ) = '$date'" );
	}

	public function copyHoursFromTo( $id_community, $dayFrom, $dayTo ){
		$shifts = Crunchbutton_Community_Shift::shiftByCommunityDay( $id_community, $dayFrom );
		foreach( $shifts as $shift ){
			$date_start = DateTime::createFromFormat( 'Y-m-d H:i:s', $dayTo . ' ' . $shift->dateStart()->format( 'H:i:s' ), new DateTimeZone( $shift->timezone() ) );	
			$date_end = DateTime::createFromFormat( 'Y-m-d H:i:s', $dayTo . ' ' . $shift->dateEnd()->format( 'H:i:s' ), new DateTimeZone( $shift->timezone() ) );	
			$newShift = new Crunchbutton_Community_Shift();
			$newShift->id_community = $id_community;
			$newShift->date_start = $date_start->format( 'Y-m-d H:i:s' );
			$newShift->date_end = $date_end->format( 'Y-m-d H:i:s' );
			$newShift->active = 1;
			if( $newShift->date_start && $newShift->date_end ){
				$newShift->save();	
			}
		}
	}

	public function timezone(){
		if( !$this->_timezone ){
			$this->_timezone = $this->community()->timezone;
		}
		return $this->_timezone;
	}

	public function timezoneAbbr(){
		$dateTime = new DateTime(); 
		$dateTime->setTimeZone( new DateTimeZone( $this->timezone() ) ); 
		return $dateTime->format( 'T' ); 
	}

	public function dateStart( $timezone = false ){

		if( $timezone ){
			$date = $this->dateStart();
			$date->setTimezone( new DateTimeZone( $timezone ) );
			return $date;
		}

		if( !$this->_date_start ){
			$this->_date_start = DateTime::createFromFormat( 'Y-m-d H:i:s', $this->date_start, new DateTimeZone( $this->timezone() ) );	
		}
		return $this->_date_start;
	}

	public function dateEnd( $timezone = false ){
		
		if( $timezone ){
			$date = $this->dateEnd();
			$date->setTimezone( new DateTimeZone( $timezone ) );
			return $date;
		}

		if( !$this->_date_end ){
			$this->_date_end = DateTime::createFromFormat( 'Y-m-d H:i:s', $this->date_end, new DateTimeZone( $this->timezone() ) );	
		}
		return $this->_date_end;
	}

	public function startEndToString( $timezone = false ){
		$time = Crunchbutton_Community_Shift::timeToSegmentString( $this->dateStart( $timezone )->format( 'H:i' ) );
		$time .= ' - ';
		$time .= Crunchbutton_Community_Shift::timeToSegmentString( $this->dateEnd( $timezone )->format( 'H:i' ) );
		return trim( $time );
	}

	public function timeToSegmentString( $time ){
		$time = explode( ':' , $time );
		$hour = $time[0];
		$min  = $time[1];
		$separator = ':';
		$ampm = 'am';
		if( $hour > 12 ){
			$hour -= 12;
			$ampm = 'pm';
		}
		if( $hour == 0 ){
			$hour = 12;
		}
		if( intval( $min ) == 0 ){
			$min = '';
			$separator = '';
		}
		return intval( $hour ) . $separator . $min . $ampm;
	}

	public function dateStartFriendly( $timezone = false ){
		return $this->dateStart( $timezone )->format( 'M jS Y g:i A T' );
	}

	public function dateEndFriendly( $timezone = false ){
		return $this->dateEnd( $timezone )->format( 'M jS Y g:i A T' );
	}

	public function fullDate( $timezone = false ){
		return 'From ' . $this->dateStartFriendly( $timezone ) . ' to ' . $this->dateEndFriendly( $timezone ); 
	}

	public function parseHour( $hour, $min, $ampm ){
		if( strtolower( $ampm ) == 'pm' ){
			if( intval( $hour ) < 12 ){
				$hour += 12;
			}
		}
		if( $hour > 24 ){
			$hour -= 24;
		}
		if( trim( $min ) == '' ){
			$min = '00';
		}
		if( trim( $hour ) != '' ){
			return $hour . ':' . $min;	
		}
		return false;
	}

	public function getAdminPreferences(){
		return Crunchbutton_Admin_Shift_Preference::q( 'SELECT * FROM admin_shift_preference WHERE id_community_shift = ' . $this->id_community_shift . ' ORDER BY ranking' );
	}

	public function getDrivers(){
		return Crunchbutton_Admin::q( 'SELECT a.* FROM admin a INNER JOIN admin_shift_assign asa ON asa.id_admin = a.id_admin AND asa.id_community_shift = ' . $this->id_community_shift );
	}

	public function communitiesWithDeliveryService(){
		return Crunchbutton_Community::q( 'SELECT DISTINCT( c.id_community ) AS id, c.* FROM community c
																				INNER JOIN restaurant_community rc ON c.id_community = rc.id_community
																				INNER JOIN restaurant r ON r.id_restaurant = rc.id_restaurant AND r.delivery_service = 1 
																			ORDER BY c.name ASC' );
	}

}

