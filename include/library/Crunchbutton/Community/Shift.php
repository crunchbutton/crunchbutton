<?php

class Crunchbutton_Community_Shift extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('community_shift')
			->idVar('id_community_shift')
			->load($id);
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

	public function saveShift( $id_community, $day, $segment ){
		if( trim( $id_community ) == '' ){
			return;
		}
		// remove old hours
		c::db()->query( 'DELETE FROM community_shift WHERE id_community=" ' . $id_community . '" AND day = "' . $day . '"');
		$segments = explode( ',' , $segment );
		foreach( $segments as $segment ){
			$segment = Crunchbutton_Community_Shift::parseSegment( $segment );
			if( $segment[ 'start' ] && $segment[ 'end' ] ){
				// save start
				$shift = new Crunchbutton_Community_Shift();
				$shift->id_community = $id_community;
				$shift->day = $day;
				$shift->start = $segment[ 'start' ];
				$shift->end = $segment[ 'end' ];
				$shift->save();
			}
		}
	}

	public function parseSegment( $segment ){
		$pattern = '@^ *(\d+)(?:\:(\d+))? *(am|pm) *(?:to|-) *(\d+)(?:\:(\d+))? *(am|pm) *$@i';
		preg_match( $pattern, $segment , $matches);
		$start = Crunchbutton_Community_Shift::parseHour( $matches[ 1 ], $matches[ 2 ], $matches[ 3 ] );
		$end = Crunchbutton_Community_Shift::parseHour( $matches[ 4 ], $matches[ 5 ], $matches[ 6 ] );
		return array( 'start' => $start, 'end' => $end );
	}

	public function startEndToSegment( $start, $end ){
		$time = Crunchbutton_Community_Shift::timeToSegmentString( $start );
		$time .= ' - ';
		$time .= Crunchbutton_Community_Shift::timeToSegmentString( $end );
		return $time;
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

