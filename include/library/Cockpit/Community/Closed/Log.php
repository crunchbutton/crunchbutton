<?php

class Cockpit_Community_Closed_Log extends Cana_Table {

	const TYPE_ALL_RESTAURANTS = 'all_restaurants';
	const TYPE_3RD_PARTY_DELIVERY_RESTAURANTS = 'close_3rd_party_delivery_restaurants';
	const TYPE_AUTO_CLOSED = 'auto_closed';
	const TYPE_CLOSED_WITH_DRIVER = 'closed_with_driver';
	const TYPE_TOTAL = 'total';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('community_closed_log')
			->idVar('id_community_closed_log')
			->load($id);
	}


	public function checkIfLogAlreadyExists( $day, $id_community, $type ){
		$log = Cockpit_Community_Closed_Log::q( 'SELECT * FROM community_closed_log WHERE day = ? AND id_community = ? AND type = ?', [$day, $id_community, $type]);
		if( $log->id_community_closed_log ){
			return true;
		}
		return false;
	}

	public function forceCloseHoursLog( $community ){
		// deprecated #7615
		return;
		if( !$community->timezone ){
			return false;
		}

		$limit_days = 30;

		$limit_date = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$limit_date->setTimezone( new DateTimeZone( $community->timezone ) );
		$limit_date->modify( '- ' . $limit_days . ' days' );

		$hours_closed = [];
		$force_closed_times = $community->forceCloseLog( false, true, $limit_days );

		foreach( $force_closed_times as $closed ){

			$from = new DateTime( $closed[ 'closed_at' ], new DateTimeZone( c::config()->timezone ) );
			$to = new DateTime( $closed[ 'opened_at' ], new DateTimeZone( c::config()->timezone ) );

			if( $to->format( 'YmdHis' ) < $limit_date->format( 'YmdHis' ) ){
				continue;
			}

			switch ( $closed[ 'type' ] ) {
				case Crunchbutton_Community::TITLE_CLOSE_ALL_RESTAURANTS:
					$type = Cockpit_Community_Closed_Log::TYPE_ALL_RESTAURANTS;
					break;
				case Crunchbutton_Community::TITLE_CLOSE_3RD_PARY_RESTAURANTS:
					$type = Cockpit_Community_Closed_Log::TYPE_3RD_PARTY_DELIVERY_RESTAURANTS;
					break;
				case Crunchbutton_Community::TITLE_CLOSE_AUTO_CLOSED:
					$type = Cockpit_Community_Closed_Log::TYPE_AUTO_CLOSED;
					break;
			}
			$hours_closed[] = [ 'from' => $from, 'to' => $to, 'type' => $type ];
		}

		$shifts = Crunchbutton_Community_Shift::q( 'SELECT * FROM community_shift cs WHERE cs.id_community = "' . $community->id_community . '" AND DATE( cs.date_end ) > "' . $limit_date->format( 'Y-m-d' ) . '" AND active = true ORDER BY cs.date_start' );
		$closed_shifts = Cockpit_Community_Closed_Log::processClosedHours( $shifts, $hours_closed );

		return $closed_shifts;
	}

	public function processClosedHours( $shifts, $hours_closed ){
		$shifts = Cockpit_Community_Closed_Log::shifts2Array( $shifts );
		$shifts = Cockpit_Community_Closed_Log::mergeShiftsHours( $shifts );
		$hours_closed = Cockpit_Community_Closed_Log::mergeShiftsWithClosedHours( $shifts, $hours_closed );
		$hours_closed = Cockpit_Community_Closed_Log::processHours( $hours_closed );
		return $hours_closed;
	}

	public function processLog(){

		$out = [];

		$communities = Crunchbutton_Community::q( 'SELECT * FROM community' );

		foreach( $communities as $community ){
			Cana::timeout(function() use($community) {
				$hours = Cockpit_Community_Closed_Log::forceCloseHoursLog( $community );
				if( count( $hours ) && is_array( $hours ) ){
					foreach( $hours as $closed_shift ) {
						Cockpit_Community_Closed_Log::saveLog( $closed_shift, $community->id_community );
					}
				}
			});
		}
	}

	public function saveLog( $closed, $id_community ){
		if( Cockpit_Community_Closed_Log::checkIfLogAlreadyExists( $closed[ 'day' ], $id_community, $closed[ 'type' ] ) == false && $closed[ 'hours' ] ){
			$log = new Cockpit_Community_Closed_Log;
			$log->id_community = $id_community;
			$log->day = $closed[ 'day' ];
			$log->hours_closed = $closed[ 'hours' ];
			$log->type = $closed[ 'type' ];
			$log->save();
		}
	}

	public function processHours( $hours ){
		$hours_closed = [];
		for( $i=0; $i< count( $hours ); $i++ ){
			$day_from = $hours[ $i ][ 'from' ]->format( 'Ymd' );
			$day_to = $hours[ $i ][ 'to' ]->format( 'Ymd' );
			$closed_from = $hours[ $i ][ 'from' ]->format( 'YmdHis' );
			$closed_to = $hours[ $i ][ 'to' ]->format( 'YmdHis' );
			if( $day_from != $day_to ){
				$_hour = $hours[ $i ];
				$_hour[ 'to' ] = new DateTime( $day_from . ' 23:59:59', $hours[ $i ][ 'from' ]->getTimezone() );
				$_hour[ 'type' ] = $hours[ $i ][ 'type' ];
				$hours_closed[] = $_hour;
				$_hour = $hours[ $i ];
				$_hour[ 'from' ] = new DateTime( $day_to . ' 00:00:01', $hours[ $i ][ 'to' ]->getTimezone() );
				$_hour[ 'type' ] = $hours[ $i ][ 'type' ];
				$hours_closed[] = $_hour;
			} else {
				$hours_closed[] = $hours[ $i ];
			}
		}

		for( $i=0; $i< count( $hours_closed ); $i++ ){
			$interval = $hours_closed[ $i ][ 'from' ]->diff( $hours_closed[ $i ][ 'to' ] );
			$hours_closed[ $i ][ 'day' ] = $hours_closed[ $i ][ 'from' ]->format( 'Y-m-d' );
			$hours_closed[ $i ][ 'hours' ] = Crunchbutton_Util::interval2Hours( $interval );
			unset( $hours_closed[ $i ][ 'to' ] );
			unset( $hours_closed[ $i ][ 'from' ] );
		}
		return $hours_closed;
	}

	public function mergeShiftsWithClosedHours( $shifts, $hours_closed ){

		$closed_times = [];
		for( $i = 0; $i < count( $shifts ); $i++ ){
			$shift_start = $shifts[ $i ]->date_start->format( 'YmdHis' );
			$shift_end = $shifts[ $i ]->date_end->format( 'YmdHis' );
			for( $j = 0; $j < count( $hours_closed ); $j++ ){
				if( !$hours_closed[ $j ][ 'from' ] || !$hours_closed[ $j ][ 'to' ] ){
					continue;
				}
				$closed_from = $hours_closed[ $j ][ 'from' ]->format( 'YmdHis' );
				$closed_to = $hours_closed[ $j ][ 'to' ]->format( 'YmdHis' );
				// { [ ] }
				if( $closed_from < $shift_start && $closed_to > $shift_end ){
					$closed_times[] = [ 'from' => $shifts[ $i ]->date_start, 'to' => $shifts[ $i ]->date_end, 'type' => $hours_closed[ $j ][ 'type' ] ];
				}
				// { [ } ]
				else if( $closed_from < $shift_start && $closed_to > $shift_start && $closed_to < $shift_end ){
					$closed_times[] = [ 'from' => $shifts[ $i ]->date_start, 'to' => $hours_closed[ $j ][ 'to' ], 'type' => $hours_closed[ $j ][ 'type' ] ];
				}
				// [ { ] }
				else if( $closed_from > $shift_start && $closed_from < $shift_end && $closed_to > $shift_end ){
					$closed_times[] = [ 'from' => $hours_closed[ $j ][ 'from' ], 'to' => $shifts[ $i ]->date_end, 'type' => $hours_closed[ $j ][ 'type' ] ];
				}
				// [ { } ]
				else if( $closed_from > $shift_start && $closed_to < $shift_end ){
					$closed_times[] = [ 'from' => $hours_closed[ $j ][ 'from' ], 'to' => $hours_closed[ $j ][ 'to' ], 'type' => $hours_closed[ $j ][ 'type' ] ];
				}
			}
		}
		return $closed_times;
	}

	public function mergeShiftsHours( $shifts ){
		if( count( $shifts ) > 1 ){
			for( $i = 0; $i < count( $shifts ); $i++ ){
				if( !$shifts[ $i ] ){ continue; }
				for( $j = 0; $j < count( $shifts ); $j++ ){
					if( !$shifts[ $j ] ){ continue; }
					if( $shifts[ $i ]->id != $shifts[ $j ]->id &&
							$shifts[ $i ]->date_start->format( 'YmdHis' ) <= $shifts[ $j ]->date_start->format( 'YmdHis' ) &&
						  $shifts[ $i ]->date_end->format( 'YmdHis' ) >= $shifts[ $j ]->date_end->format( 'YmdHis' ) ){
							unset( $shifts[ $j ] );
							return Cockpit_Community_Closed_Log::mergeShiftsHours( $shifts );
					} else
					if( $shifts[ $i ]->id != $shifts[ $j ]->id &&
							$shifts[ $i ]->date_start->format( 'YmdHis' ) <= $shifts[ $j ]->date_start->format( 'YmdHis' ) &&
							$shifts[ $i ]->date_end->format( 'YmdHis' ) <= $shifts[ $j ]->date_end->format( 'YmdHis' ) &&
							$shifts[ $i ]->date_end->format( 'YmdHis' ) >= $shifts[ $j ]->date_start->format( 'YmdHis' ) ){
							$shifts[ $i ]->date_end = $shifts[ $j ]->date_end;
							unset( $shifts[ $j ] );
						return Cockpit_Community_Closed_Log::mergeShiftsHours( $shifts );
					}
				}
			}
			usort( $shifts, function( $a, $b ) {
				return ( $a->date_start->format( 'YmdHis' ) > $b->date_start->format( 'YmdHis' ) );
			} );
		}
		for( $i=0; $i< count( $shifts ); $i++ ){
			unset( $shifts[ $i ]->id );
			$interval = $shifts[ $i ]->date_start->diff( $shifts[ $i ]->date_end );
			$shifts[ $i ]->hours = Crunchbutton_Util::interval2Hours( $interval );
		}
		return $shifts;
	}

	public function shifts2Array( $shifts ){
		$out = [];
		foreach( $shifts as $shift ){
			$_shift = [];
			$_shift[ 'id' ] = $shift->id_community_shift;
			$_shift[ 'date_start' ] = $shift->dateStart( c::config()->timezone );
			$_shift[ 'date_end' ] = $shift->dateEnd( c::config()->timezone );
			$out[] = ( object ) $_shift;
		}
		return $out;
	}
}