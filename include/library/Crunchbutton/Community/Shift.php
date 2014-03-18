<?php

class Crunchbutton_Community_Shift extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('community_shift')
			->idVar('id_community_shift')
			->load($id);
	}

	public function shiftByCommunityDay( $id_community, $date ){
		return Crunchbutton_Community_Shift::q( 'SELECT * FROM community_shift WHERE id_community = "' . $id_community . '" AND DATE_FORMAT( date_start, "%Y-%m-%d" ) = "' . $date . '" ORDER BY id_community_shift ASC' );
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

	public function timezone(){
		if( !$this->_timezone ){
			$this->_timezone = $this->community()->timezone;
		}
		return $this->_timezone;
	}

	public function dateStart(){

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

	public function copyHoursFromTo( $from, $to ){
		// continue here
	}

	public function timezoneAbbr(){
		$dateTime = new DateTime(); 
		$dateTime->setTimeZone( new DateTimeZone( $this->timezone() ) ); 
		return $dateTime->format( 'T' ); 
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
		if( intval( $min ) == 0 ){
			$min = '';
			$separator = '';
		}
		return $hour . $separator . $min . $ampm;
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


}

