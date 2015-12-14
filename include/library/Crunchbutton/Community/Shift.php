<?php

class Crunchbutton_Community_Shift extends Cana_Table {

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('community_shift')
			->idVar('id_community_shift')
			->load($id);
	}

	public function exports() {
		$out = $this->properties();
		$out['community_name'] = $this->community()->name;
		return $out;
	}

	public function isShiftCreatedByDriver(){
		return ( $this->id_driver ? true : false );
	}

	public function isVisible(){
		return ( intval( $this->hidden ) == 0 );
	}

	public function isHidden(){
		return !$this->isVisible();
	}

	public static function driversCouldDeliveryOrder( $id_order ){
		if( !$id_order ){
			return false;
		}

		$order = Order::o( $id_order );
		if( $order->restaurant()->timezone ){
			$now = new DateTime( 'now', new DateTimeZone( $order->restaurant()->timezone ) );
		} else {
			$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		}

		return Admin::q( 'SELECT a.* FROM admin a
												INNER JOIN admin_shift_assign asa ON asa.id_admin = a.id_admin
												INNER JOIN community_shift cs ON cs.id_community_shift = asa.id_community_shift
												INNER JOIN restaurant_community rc ON rc.id_community = cs.id_community
												INNER JOIN `order` o ON o.id_restaurant = rc.id_restaurant
											WHERE o.id_order = ? AND cs.date_start <= ? AND cs.date_end >= ? AND cs.active = true AND a.active = true ', [$id_order, $now->format( 'Y-m-d H:i:s' ), $now->format( 'Y-m-d H:i:s' )]);
	}

	public function duration( $timeIn = 'hours' ){
		$secs = Util::intervalToSeconds( $this->dateEnd()->diff( $this->dateStart() ) );
		if( $timeIn == 'hours' ){
			return $secs / 60 / 60;
		}
	}

	public function export(){
		$out = [];

		$out[ 'community' ] = array( 'id_community' => $this->id_community, 'name' => $this->community()->name );

		$out[ 'period' ] = array( 'toString' => $this->startEndToString(),
															'weekday' => $this->dateStart()->format( 'l' ),
															'day_start' => $this->dateStart()->format( 'M jS Y' ),
															'day_end' => $this->dateEnd()->format( 'M jS Y' ),
															'date_start' => $this->dateStart()->format( 'Y-m-d H:i:s' ),
															'date_end' => $this->dateEnd()->format( 'Y-m-d H:i:s' ),
															'timezone' => $this->timezone(),
															'timezone_abbr' => $this->timezoneAbbr() );

		$out[ 'period_pst' ] = array( 'toString' => $this->startEndToString( c::config()->timezone ),
																	'day_start' => $this->dateStart( c::config()->timezone )->format( 'M jS Y' ),
																	'day_end' => $this->dateEnd( c::config()->timezone )->format( 'M jS Y' ),
																	'date_start' => $this->dateStart( c::config()->timezone )->format( 'Y-m-d H:i:s' ),
																	'date_end' => $this->dateEnd( c::config()->timezone )->format( 'Y-m-d H:i:s' ),
																	'timezone' => c::config()->timezone,
																	'timezone_abbr' => 'PST' );
		return $out;
	}

	public static function lastShiftsByAdmin( $id_admin, $limit = 10 ){
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone  ) );
		$now->modify( '+ 1 day' );
		$query = '
			SELECT cs.*, ass.id_admin_shift_assign, ass.confirmed FROM admin_shift_assign ass
			INNER JOIN community_shift cs ON cs.id_community_shift = ass.id_community_shift
			WHERE
				ass.id_admin = ?
				AND cs.date_start <= ?
			ORDER BY cs.date_start DESC
			LIMIT ?
		';
		return Crunchbutton_Community_Shift::q( $query, [$id_admin, $now->format( 'Y-m-d' ), $limit]);
	}

	public static function nextShiftsByAdmin( $id_admin, $limit = 20 ){
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone  ) );
		$query = '
			SELECT cs.*, ass.id_admin_shift_assign, ass.confirmed FROM admin_shift_assign ass
			INNER JOIN community_shift cs ON cs.id_community_shift = ass.id_community_shift
			WHERE
				ass.id_admin = ?
				AND cs.date_start >= ?
			ORDER BY cs.date_start ASC
			LIMIT ' . $limit;
		return Crunchbutton_Community_Shift::q( $query, [$id_admin, $now->format( 'Y-m-d' )]);
	}

	public function isConfirmed(){
		$assignment = Crunchbutton_Admin_Shift_Assign::q( 'SELECT * FROM admin_shift_assign WHERE id_community_shift = ? AND confirmed = 1 ORDER BY id_admin_shift_assign DESC LIMIT 1', [ $this->id_community_shift ])->get(0);
		if( $assignment->id_admin_shift_assign ){
			return true;
		}
		return false;
	}

	public static function nextShiftsByCommunities( $communities ){
		if( count( $communities ) > 0 ){
			$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone  ) );
			$now_formated = $now->format( 'Y-m-d' );
			$now->modify( '+ 7 days' );
			$next_days_formated = $now->format( 'Y-m-d' );
			$query = '
				SELECT cs.* FROM community_shift cs
				WHERE
					cs.id_community IN( ' . join( ',', $communities ) . ' )
					AND cs.date_start >= ?
					AND cs.date_start <= ?
				ORDER BY cs.date_start ASC
			';
			return Crunchbutton_Community_Shift::q($query, [$now_formated, $next_days_formated]);
		}
		return false;
	}

	public static function nextAssignedShiftByCommunity( $id_community ){
			$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone  ) );
			$community = Crunchbutton_Community::o( $id_community );
			$now->setTimezone( new DateTimeZone( $community->timezone ) );
			$now_formated = $now->format( 'Y-m-d H:i:s' );
			$query = '
				SELECT cs.* FROM community_shift cs
					INNER JOIN admin_shift_assign asa ON cs.id_community_shift = asa.id_community_shift
					INNER JOIN admin a ON asa.id_admin = a.id_admin AND a.active = true
				WHERE
					cs.id_community = ?
					AND cs.date_start >= ?
					AND cs.active = true
				ORDER BY cs.date_start ASC
				LIMIT 1
			';

			$shift = Crunchbutton_Community_Shift::q( $query, [$id_community, $now_formated])->get(0);

			if ($shift->id_community){
				$date_start = $shift->dateStart()->format( 'Y-m-d' ) . ' 00:00:00';
				$date_end = $shift->dateStart()->format( 'Y-m-d' ) . ' 23:59:59';
				$query = '
					SELECT cs.* FROM community_shift cs
						INNER JOIN admin_shift_assign asa ON cs.id_community_shift = asa.id_community_shift
						INNER JOIN admin a ON asa.id_admin = a.id_admin AND a.active = true
					WHERE
						cs.id_community = ?
						AND cs.date_start BETWEEN ? AND ?
						AND cs.active = true
						AND cs.id_community_shift != ?
					ORDER BY cs.date_start ASC
				';

				$shifts = Crunchbutton_Community_Shift::q( $query, [$id_community, $date_start, $date_end, $shift->id_community_shift]);

				if( $shifts->count() > 0 ){
					$last_date = $shift->dateEnd();
					foreach( $shifts as $_shift ){
						$_start_date = $_shift->dateStart();
						$_last_date = $_shift->dateEnd();
						if( $_start_date->format( 'YmdHis' ) <= $shift->dateEnd()->format( 'YmdHis' ) &&
								$last_date->format( 'YmdHis' ) < $_last_date->format( 'YmdHis' ) ){
							$last_date = $_last_date;
						}
					}
					$shift->_date_end = false;
					$shift->date_end = $last_date->format( 'Y-m-d H:i:s' );
				}
				return $shift;
			}
			return false;
	}

	public static function currentAssignedShiftByCommunity( $id_community ){
			$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone  ) );
			$community = Crunchbutton_Community::o( $id_community );
			$timezone = ( $community->timezone ) ? $community->timezone : c::config()->timezone;
			$now->setTimezone( new DateTimeZone( $timezone ) );
			$now_formated = $now->format( 'Y-m-d H:i:s' );
			$query = 'SELECT cs.* FROM community_shift cs
									INNER JOIN admin_shift_assign asa ON cs.id_community_shift = asa.id_community_shift
									INNER JOIN admin a ON asa.id_admin = a.id_admin AND a.active = true
								WHERE
									cs.id_community = "' . $id_community . '"
									AND cs.date_start <= "' . $now_formated . '"
									AND cs.date_end >= "' . $now_formated . '"
									AND cs.active = true
								ORDER BY cs.date_start';
			$shift = Crunchbutton_Community_Shift::q( $query );
			if( $shift->id_community ){
				return $shift;
			}
	}

	public function shiftsByDay( $date ){
		Crunchbutton_Community_Shift::createRecurringEvent( $date );
		return Crunchbutton_Community_Shift::q( 'SELECT cs.* FROM community_shift cs INNER JOIN community c ON c.id_community = cs.id_community WHERE cs.date_start BETWEEN \'' . $date . ' 00:00:00\' AND \'' . $date . ' 23:59:59\' AND cs.active = true ORDER BY c.name, cs.date_start ASC' );
	}

	public function shiftsByCommunityPeriod( $id_community, $from, $to ){
		return Crunchbutton_Community_Shift::q( 'SELECT cs.* FROM community_shift cs WHERE cs.date_start >= \'' . $from . ' 00:00:00\' AND cs.date_end <= \'' . $to . ' 23:59:59\' AND id_community = ? ORDER BY cs.date_start ASC', [$id_community]);
	}

	public function assignedShiftsByCommunityPeriod( $id_community, $from, $to ){
		return Crunchbutton_Community_Shift::q( 'SELECT DISTINCT( cs.id_community_shift ), cs.* FROM community_shift cs  INNER JOIN admin_shift_assign asa ON asa.id_community_shift = cs.id_community_shift WHERE cs.date_start >= \'' . $from . ' 00:00:00\' AND cs.date_end <= \'' . $to . ' 23:59:59\' AND id_community = ? ORDER BY cs.date_start ASC', [$id_community]);
	}

	public function shiftsByDriverByPeriod( $id_admin, $from, $to ){
		return Crunchbutton_Community_Shift::q( 'SELECT cs.* FROM community_shift cs INNER JOIN admin_shift_assign asa ON asa.id_community_shift = cs.id_community_shift WHERE cs.date_start >= \'' . $from . ' 00:00:00\' AND cs.date_end <= \'' . $to . ' 23:59:59\' AND asa.id_admin = ? ORDER BY cs.date_start ASC', [$id_admin]);
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

	public function getLastWorkedShiftByAdmin($id_admin, $now = 'now') {
		$admin = Crunchbutton_Admin::o($id_admin);
		$timezone = $admin->timezone();
		$now = new DateTime($now, $timezone);

		$params = [$id_admin, $now->format('Y-m-d H:i')];

		$query = '
			SELECT cs.* FROM admin_shift_assign asa
			INNER JOIN community_shift cs ON cs.id_community_shift = asa.id_community_shift
			WHERE
				asa.id_admin = ?
				AND cs.date_start <= ?
			ORDER BY cs.date_start DESC
			LIMIT 1
		';

		return Crunchbutton_Community_Shift::q($query, $params);
	}

	public static function shiftDriverIsCurrentWorkingOn($id_admin, $dt = null, $id_community = null, $checkIfTheyCheckedIn = false) {

		$admin = Admin::o($id_admin);

		// start using community's timezone - #4965
		$community = $admin->communityDriverDelivery();

		if ($community->id_community && $community->timezone) {
			$timezone = $community->timezone;
		} else {
			$timezone = $admin->timezone;
		}

		if ($timezone) {
			$time = ($dt ? $dt : 'now');
			$now = new DateTime( $time, new DateTimeZone( c::config()->timezone ) );
			$now->setTimezone( new DateTimeZone( $timezone ) );
			$nowFormat = $now->format( 'Y-m-d H:i' );

			$params = [$id_admin, $nowFormat, $nowFormat];

			$query = '
				SELECT cs.*, asa.id_admin_shift_assign, asa.confirmed FROM community_shift cs
				INNER JOIN admin_shift_assign asa ON asa.id_community_shift = cs.id_community_shift
				WHERE asa.id_admin = ?
					AND cs.active = true
					AND cs.date_start <= ?
	 				AND cs.date_end >= ?
			';

			if ($id_community) {
				$query .= ' AND cs.id_community = ? ';
				$params[] = $id_community;
			}

			if( $checkIfTheyCheckedIn ){
				$query .= ' AND asa.confirmed = 1 ';
			}

	 		$shift = Crunchbutton_Community_Shift::q($query, $params);
	 		if ($shift->id_admin_shift_assign) {
	 			if( $checkIfTheyCheckedIn ){
	 				if( $shift->confirmed ){
	 					return $shift;
	 				}
	 			} else {
	 				return $shift;
	 			}

	 		}
		}

 		return false;
	}

	public function getCurrentShiftByAdmin( $id_admin ){
		$query = '
			SELECT cs.* FROM admin_shift_assign asa
			INNER JOIN community_shift cs ON cs.id_community_shift = asa.id_community_shift
			WHERE asa.id_admin = ?
				AND cs.date_start <= NOW()
			ORDER BY cs.date_start DESC
			LIMIT 1
		';
		return Crunchbutton_Community_Shift::q( $query, [$id_admin]);
	}

	public function isRecurring(){
		if( $this->recurringId() ){
			return true;
		}
	}

	public function recurringId(){
		if( $this->recurring ){
			return $this->id_community_shift;
		}
		if( $this->id_community_shift_father ){
			return $this->id_community_shift_father;
		}
		return false;
	}

	public function shiftByCommunityDay( $id_community, $date ){

		$_date = new DateTime( $date, new DateTimeZone( c::config()->timezone ) );

		$now = $_date->format( 'Y-m-d' );
		$_date->modify( '+1 day' );
		$next = $_date->format( 'Y-m-d' );

		Crunchbutton_Community_Shift::createRecurringEvent( $date );
		return Crunchbutton_Community_Shift::q('
			SELECT * FROM community_shift
			WHERE id_community = ?
				AND date_start >= ?
				AND date_start < ?
				AND active = true
			ORDER BY id_community_shift ASC ', [ $id_community, $now, $next ]);
	}

	public function createRecurringEvent( $date ){
		// Search for recurring events
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$day = new DateTime( $date, new DateTimeZone( c::config()->timezone ) );
		$weekday = $day->format( 'w' );

		if( Crunchbutton_Util::isMySQL() ){
			$query = ' SELECT * FROM community_shift
										WHERE
									recurring = true
									AND active = true
									AND DATE_FORMAT( date_start, "%w" ) = ? ';
		} else {
			// postgree
			$query = ' SELECT * FROM community_shift
										WHERE
									recurring = true
									AND active = true
									AND extract( dow from date_added ) = ? ';
		}

		$shifts = Crunchbutton_Community_Shift::q( $query, [ intval( $weekday ) ] );

		// Create the recurring events
		foreach( $shifts as $shift ){
			if( $shift->dateStart()->format( 'Ymd' ) < $day->format( 'Ymd' ) ){
				$date_base = DateTime::createFromFormat( 'Y-m-d H:i:s', $date . ' 00:00:00', new DateTimeZone( c::config()->timezone ) );
				$hours = Crunchbutton_Admin_Hour::segmentToDate( $date_base, $shift->startEndToString(), $timezone );
				if( $hours ){
					// Check if the recurring event was already created
					$checkShift = Crunchbutton_Community_Shift::q('
						SELECT * FROM community_shift
						WHERE
							id_community_shift_father = ?
							AND date_start = ? AND date_end = ?
					', [$shift->id_community_shift, $hours[ 'start' ], $hours[ 'end' ]]);

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

						// Create the permanent assigments
						$permanentlys = Crunchbutton_Admin_Shift_Assign_Permanently::getByShift( $shift->id_community_shift );
						foreach( $permanentlys as $permanently ){
							if( $permanently->id_admin ){
								if( !Crunchbutton_Admin_Shift_Assign_Permanently_Removed::wasRemoved( $newShift->id_community_shift, $permanently->id_admin ) ){
									$assignment = new Crunchbutton_Admin_Shift_Assign();
									$assignment->id_admin = $permanently->id_admin;
									$assignment->id_community_shift = $newShift->id_community_shift;
									$assignment->date = date('Y-m-d H:i:s');
									$assignment->save();
								}
							}
						}
					} else {

						if( $now < $checkShift->dateStart()->get( 0 ) ){
							// Create the permanent assigments
							$permanentlys = Crunchbutton_Admin_Shift_Assign_Permanently::getByShift( $shift->id_community_shift );
							foreach( $permanentlys as $permanently ){
								if( $permanently->id_admin ){
									if( !Crunchbutton_Admin_Shift_Assign::adminHasShift( $permanently->id_admin, $checkShift->id_community_shift ) ){
										if( !Crunchbutton_Admin_Shift_Assign_Permanently_Removed::wasRemoved( $checkShift->id_community_shift, $permanently->id_admin ) ){
											$assignment = new Crunchbutton_Admin_Shift_Assign();
											$assignment->id_admin = $permanently->id_admin;
											$assignment->id_community_shift = $checkShift->id_community_shift;
											$assignment->date = date('Y-m-d H:i:s');
											$assignment->save();
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}

	public function shiftByCommunity( $id_community ){
		$weekdays = [ 'mon' =>  false, 'tue' =>  false, 'wed' =>  false, 'thu' =>  false, 'fri' =>  false, 'sat' =>  false, 'sun'  =>  false ];
		foreach( $weekdays as $day => $val ){
			$shifts = Crunchbutton_Community_Shift::q('
				SELECT * FROM community_shift
				WHERE
					id_community = ?
					AND day = ?
				ORDER BY id_community_shift ASC
			', [$id_community, $day]);
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
		$now =  new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		if( $shift->id_community_shift_father ){
			c::db()->query( "UPDATE community_shift SET id_community_shift_father = NULL, recurring = 0 WHERE date_start >= '" . $now->format( 'Y-m-d' ) . "' AND id_community_shift = " . $shift->id_community_shift_father );
			c::db()->query( "UPDATE community_shift SET id_community_shift_father = NULL WHERE date_start >= '" . $now->format( 'Y-m-d' ) . "' AND id_community_shift_father = " . $shift->id_community_shift_father );
			c::db()->query( 'DELETE FROM admin_shift_assign_permanently WHERE id_community_shift = ' . $shift->id_community_shift_father  );
		}
		if( $shift->recurring ){
			c::db()->query( "UPDATE community_shift SET id_community_shift_father = NULL, recurring = 0 WHERE date_start >= '" . $now->format( 'Y-m-d' ) . "' AND id_community_shift = " . $shift->id_community_shift );
			c::db()->query( "UPDATE community_shift SET id_community_shift_father = NULL WHERE date_start >= '" . $now->format( 'Y-m-d' ) . "' AND id_community_shift_father = " . $shift->id_community_shift );
			c::db()->query( 'DELETE FROM admin_shift_assign_permanently WHERE id_community_shift = ' . $shift->id_community_shift  );
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
			c::db()->query( "UPDATE community_shift SET active = false WHERE id_community_shift = " . $shift->id_community_shift_father );
			Crunchbutton_Community_Shift::removeRecurringChildren( $shift->id_community_shift_father );
		} else {
			c::db()->query( "DELETE FROM community_shift WHERE id_community_shift = " . $id_community_shift );
		}
	}

	public function removeHoursFromDay( $id_community, $date ){
		return c::db()->query( "DELETE FROM community_shift WHERE id_community = $id_community AND date_start BETWEEN '{$date} 00:00:00' AND '{$date} 23:59:59'" );
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

	public function currentShiftByCommunity( $id_community ){
		$community = Crunchbutton_Community::o( $id_community );
		if( $community->id_community ){
			$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
			$now->setTimeZone( new DateTimeZone( $community->timezone ) );
			$shifts = Crunchbutton_Community_Shift::q( 'SELECT * FROM community_shift WHERE id_community = ' . $id_community . ' AND date_start >= "' . $now->format( 'Y-m-d H:i:s' ) . '" LIMIT 1' );
			return $shifts;
		}
		return false;

	}

	public function dateStart( $timezone = false ){

		if( $timezone ){
			$date = $this->dateStart();
			$date->setTimezone( new DateTimeZone( $timezone ) );
			return $date;
		}
		return DateTime::createFromFormat( 'Y-m-d H:i:s', $this->date_start, new DateTimeZone( $this->timezone() ) );
	}

	public function dateEnd( $timezone = false ){

		if( $timezone ){
			$date = $this->dateEnd();
			$date->setTimezone( new DateTimeZone( $timezone ) );
			return $date;
		}
		return DateTime::createFromFormat( 'Y-m-d H:i:s', $this->date_end, new DateTimeZone( $this->timezone() ) );
	}

	public function startEndToStringCommunityTz(){
		return [ 'start' => $this->dateStartFriendly(), 'end' => $this->dateEndFriendly() ];
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
		} else if( $hour == 12 ){
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
		return Crunchbutton_Admin_Shift_Preference::q( 'SELECT asp.* FROM admin_shift_preference asp INNER JOIN admin a ON a.id_admin = asp.id_admin AND a.active = true  WHERE id_community_shift = ' . $this->id_community_shift . ' ORDER BY ranking  DESC' );
	}

	public function getAdminPreferencesByDriver( $id_admin ){
		return Crunchbutton_Admin_Shift_Preference::q( 'SELECT * FROM admin_shift_preference WHERE id_community_shift = ' . $this->id_community_shift . ' AND id_admin = ' . $id_admin . ' ORDER BY ranking  DESC' );
	}

	public function getDrivers(){
		return Crunchbutton_Admin::q('
			SELECT admin.* FROM admin
			LEFT JOIN admin_shift_assign ON admin_shift_assign.id_admin = admin.id_admin
			LEFT JOIN community_shift ON community_shift.id_community_shift = admin_shift_assign.id_community_shift
			WHERE
				admin.active=true
				AND community_shift.active=true
				AND community_shift.id_community_shift='.$this->id_community_shift.'
		');
	}

	public function communitiesWithDeliveryService(){
		return Crunchbutton_Community::q( 'SELECT * FROM (
																				SELECT DISTINCT( c.id_community ) AS id, c.* FROM community c WHERE c.id_community = ' . Crunchbutton_Community::CUSTOMER_SERVICE_ID_COMMUNITY . '
																				UNION ALL
																				SELECT DISTINCT( c.id_community ) AS id, c.* FROM community c
																					INNER JOIN restaurant_community rc ON c.id_community = rc.id_community
																					INNER JOIN restaurant r ON r.id_restaurant = rc.id_restaurant AND r.delivery_service = true ) drivers ORDER BY drivers.name' );
	}

	public function deliveredOrdersByAdminAtTheShift( $id_admin ){
		$dateStart = $this->dateStart( c::config()->timezone );
		$dateEnd = $this->dateEnd( c::config()->timezone );
		$query = 'SELECT o.* FROM `order` o
							INNER JOIN order_action oa ON oa.id_order = o.id_order
							WHERE o.date >= "' . $dateStart->format( 'Y-m-d H:i:s' ) . '" AND o.date <= "' . $dateEnd->format( 'Y-m-d H:i:s' ) . '"
							AND oa.type = "' . Crunchbutton_Order_Action::DELIVERY_DELIVERED . '" AND oa.id_admin = "' . $id_admin . '"';
		$orders = Crunchbutton_Order::q( $query );
		return $orders;
	}

	public static function getShiftsStatus( $id_admin ){
		// Start week on Thursday #3084
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone  ) );

		if( $now->format( 'l' ) == 'Thursday' ){
			$thursday = $now;
			$thursday->modify( '+ 1 week' );
		} else {
			$thursday = new DateTime( 'next thursday', new DateTimeZone( c::config()->timezone  ) );
		}

		$year = $thursday->format( 'Y' );
		$week = $thursday->format( 'W' );

		$firstDay = $thursday;
		$shifts_period = $firstDay->format( 'l m/d' );
		$firstDay->modify( '+ 6 days' );
		$shifts_period .= '-' . $firstDay->format( 'l m/d' );

		$firstDay->modify( '- 6 days' );

		$days = [];
		for( $i = 0; $i <= 6; $i++ ){
			$days[] = new DateTime( $firstDay->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
			$firstDay->modify( '+ 1 day' );
		}

		$wantToWork = [];
		$donWantToWork = [];

		$from = $days[ 0 ];
		$to = $days[ 6 ];

		return Crunchbutton_Admin_Shift_Preference::shiftsByPeriod( $id_admin, $from->format( 'Y-m-d' ), $to->format( 'Y-m-d' ) );
	}

	public function sendWarningToDrivers(){

		$warningDrivers = false;
		$warningCS = false;

		$weekday = date( 'l' );
		$time = date( 'Hi' );

		// Messages defined here:
		// https://github.com/crunchbutton/crunchbutton/issues/3084#issuecomment-44353387
		switch ( $weekday ) {
			case 'Sunday':
				// Sent on Sun at 1 PM PDT
				if( $time >= 1300 && $time <= 1359 ){
					$driversMessage = 'Hey [name]! Remember to fill out your Crunchbutton shift preferences for this Thurs—>next Wed at cockpit.la/schedule.|Due tomorrow at 5 PM PT. If you have any questions, just text back.';
					$warningDrivers = true;
				}
				// Sent on Sun at 6 PM PDT
				else if ( $time >= 1800 && $time <= 1859 ){
					$driversMessage = '[name], Don’t forget: fill out your Crunchbutton shift preferences for this Thurs—>next Wed at cockpit.la/schedule. Got a question? Text us back.';
					$warningDrivers = true;
				}
				break;
			case 'Monday':
				// Sent on Mon at 10 AM PDT
				if( $time >= 1000 && $time <= 1559 ){
					$driversMessage = '[name], Remember: fill out your Crunchbutton shift preferences for this Thurs—>next Wed at cockpit.la/schedule. Due tonight at 5 PM PT';
					$warningDrivers = true;
				}
				// Sent on Mon at 4 PM PDT
				else if( $time >= 1600 && $time <= 1654 ){
					$driversMessage = '[name], Due in 1 hour, if you have not already: Crunchbutton shift preferences for this Thurs—>next Wed at cockpit.la/schedule. If you have any questions, just text back.';
					$warningDrivers = true;
				}
				// Sent on Mon at 4:55 PM PDT
				else if( $time >= 1655 && $time <= 1659 ){
					$driversMessage = '[name], Reminder, if you have not already: your shift preferences are due in 5 min!! cockpit.la/schedule. Got a question? Text us back.';
					$warningDrivers = true;
				}
				// Sent on Mon at 5 PM PDT - alert CS
				else if ( $time >= 1700 && $time <= 1800 ){
					$warningCS = true;
				}
				break;
		}

		if( !$warningCS && !$driversMessage ){
			return;
		}

		// Start week on Thursday #3084
		$day = new DateTime( 'next thursday', new DateTimeZone( c::config()->timezone ) );

		$_week = $day->format( 'W' );
		$_year = $day->format( 'Y' );

		$from = new DateTime( $day->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone ) );
		$day->modify( '+6 day' );
		$to = new DateTime( $day->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone ) );

		$log = 'Starting the driver schedule verification period from ' . $from->format( 'Y-m-d' ) . ' to ' . $to->format( 'Y-m-d' ) . ' at ' . date( 'Y-m-d H:i:s l' );
		Log::debug( [ 'action' => $log, 'type' => 'driver-schedule' ] );
		echo $log."\n";

		$communitiesWithShift = [];
		$communitiesWithoutShift = [];

		$driversWillReceiveTheNotification = [];

		// Get the communities with active and delivery_service restaurants
		$communities = Crunchbutton_Community::q( 'SELECT DISTINCT( c.id_community ) AS id, c.* FROM community c INNER JOIN restaurant_community rc ON rc.id_community = c.id_community INNER JOIN restaurant r ON r.id_restaurant = rc.id_restaurant WHERE r.active = true AND r.delivery_service = true AND c.id_community != "' . Crunchbutton_Community::CUSTOMER_SERVICE_ID_COMMUNITY . '" ORDER BY c.name' );

		foreach( $communities as $community ){

			// Check if the community has shift for current week
			$shifts = Crunchbutton_Community_Shift::shiftsByCommunityPeriod( $community->id_community, $from->format( 'Y-m-d' ), $to->format( 'Y-m-d' ) );

			$drivers = $community->getDriversOfCommunity();

			echo "\n";
			$log = $community->name . ' has ' .  $drivers->count() . ' drivers';
			Log::debug( [ 'action' => $log, 'type' => 'driver-schedule' ] );
			echo $log."\n";
			echo "\n";

			foreach( $drivers as $driver ){

				if( !$driver->isDriver() ){
					continue;
				}

				$receiveSMS = $driver->getConfig( Crunchbutton_Admin::CONFIG_RECEIVE_DRIVER_SCHEDULE_SMS_WARNING );
				if( $receiveSMS->id_admin_config ){
					$receiveSMS = $receiveSMS->value;
				} else {
					$receiveSMS = false;
				}
				if( $receiveSMS ){

					$preferences = Crunchbutton_Admin_Shift_Status::getByAdminWeekYear( $driver->id_admin, $_week, $_year );

					$log = 'driver set up to RECEIVE sms: ' . $driver->name . ' - ' . $community->name;
					Log::debug( [ 'action' => $log, 'type' => 'driver-schedule' ] );
					echo $log."\n";
					if( $preferences->completed == 1 ){
						$log = 'driver Already finished its schedule: ' . $driver->name;
						Log::debug( [ 'action' => $log, 'type' => 'driver-schedule' ] );
						echo $log."\n";
					} else {
						$rankedShifts = self::getShiftsStatus( $driver->id_admin );
						if( $rankedShifts && $rankedShifts->count() ){
							$log = 'driver Already ranked some shifts: ' . $driver->name . ': ' . $rankedShifts->count();
							Log::debug( [ 'action' => $log, 'type' => 'driver-schedule' ] );
							echo $log."\n";
						} else {
							$driversWillReceiveTheNotification[] = array(  'id_admin' => $driver->id_admin, 'name' => $driver->name, 'txt' => $driver->txt, 'phone' => $driver->phone );
						}
					}
				} else {
					$log = 'driver set up to NOT receive sms: ' . $driver->name;
					Log::debug( [ 'action' => $log, 'type' => 'driver-schedule' ] );
					echo $log."\n";
				}
			}
			if( $shifts->count() == 0 ){
				$communitiesWithoutShift[] = $community->name;
			}
		}

		echo "\n";
		$log = 'communitiesWithoutShift: ' . count( $communitiesWithoutShift ) . ', list ' . join( ', ', $communitiesWithoutShift );
		Log::debug( [ 'action' => $log, 'type' => 'driver-schedule' ] );
		echo $log."\n";

		echo "\n";
		$log = 'driversWillReceiveTheNotification: ' . count( $driversWillReceiveTheNotification );
		Log::debug( [ 'action' => $log, 'type' => 'driver-schedule' ] );
		echo $log."\n";

		$env = c::getEnv();

		$twilio = new Services_Twilio( c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token );

		// removed this sms for while

		if( false && count( $communitiesWithoutShift ) > 0 ){

			$message = Crunchbutton_Message_Sms::greeting() . 'The following communities doesn\'t have shifts for the current week: ' . join( ', ', $communitiesWithoutShift );

			Crunchbutton_Message_Sms::send( [ 'to' => Crunchbutton_Support::getUsers(), 'message' => $message, 'reason' => Crunchbutton_Message_Sms::REASON_SUPPORT_WARNING ] );
		}

		echo "\n";
		Log::debug( [ 'action' => 'notification sms', 'total' => count( $driversWillReceiveTheNotification ), 'type' => 'driver-schedule' ] );

		if( $warningCS && count( $driversWillReceiveTheNotification ) > 0 ){

			$message = Crunchbutton_Message_Sms::greeting() . count( $driversWillReceiveTheNotification ) . ' drivers didn\'t completed their schedule, the list of drivers is available here at cockpit._DOMAIN_/drivers/shift/status/shift';

			echo "Sending sms to support users...\n";

			$rets = Crunchbutton_Message_Sms::send( [ 'to' => Crunchbutton_Support::getUsers(), 'message' => $message, 'reason' => Crunchbutton_Message_Sms::REASON_SUPPORT_WARNING ] );

			foreach ($rets as $ret) {
				if (!$ret->sid) {
					echo 'Error Sending sms to: '.$ret->to;
				}
			}
		}

		if( $warningDrivers ){

			foreach( $driversWillReceiveTheNotification as $driver ){

				$id_admin = $driver[ 'id_admin' ];
				$name = $driver[ 'name' ];
				$txt = $driver[ 'txt' ];
				$phone = $driver[ 'phone' ];

				$message = str_replace( '[name]' , $name, $driversMessage );

				$num = ( $txt != '' ) ? $txt : $phone;

				$cs_message = 'Driver notificaton: ' . str_replace( '|', '<br>',  $message );
				// Crunchbutton_Support::createNewWarning(  [ 'dont_open_ticket' => false, 'body' => $cs_message, 'phone' => $num ] );

				if (strpos( $message, '|') > 0) {
					$message = str_replace('|', "\n", $message);
				}
				echo $message;

				// #4060 - dont send from driver number
				$rets = Crunchbutton_Message_Sms::send([
					'to' => $num,
					'message' => $message,
					'reason' => Crunchbutton_Message_Sms::REASON_DRIVER_SHIFT
				]);

				if( $rets ){
					foreach ($rets as $ret) {
						if (!$ret->sid) {
							echo 'Error Sending sms to: '.$ret->to;
						}
					}
				}
			}
		}
	}

	// remind drivers about their shift the day before their shift #2816
	function remindDriversAboutTheirShiftTomorrow(){

		echo "\n";
		$log = 'Starting the remind of tomorrow\'s shift';
		Log::debug( [ 'action' => $log, 'type' => 'driver-remind' ] );
		echo $log."\n";
		echo "\n";

		$messagePattern = "Remember: you're scheduled to drive for Crunchbutton tomorrow, %s, from %s\nRemember to charge your phone!";

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone  ) );
		$now->modify( '+ 1 day' );

		$adminsWithShifts = Crunchbutton_Admin::q( 'SELECT DISTINCT( asa.id_admin )
																									FROM admin_shift_assign asa
																									INNER JOIN community_shift cs ON asa.id_community_shift = cs.id_community_shift AND cs.id_community != "' . Crunchbutton_Community::CUSTOMER_SERVICE_ID_COMMUNITY . '"
																									WHERE ( cs.hidden != 1 ) AND cs.date_start BETWEEN "' . $now->format( 'Y-m-d' ) . ' 00:00:00" AND "' . $now->format( 'Y-m-d' ) . ' 23:59:59" AND cs.active = true' );

		$now_start = $now->format( 'Y-m-d' ) . ' 00:00:00';
		$now_end = $now->format( 'Y-m-d' ) . ' 23:59:59';

		foreach( $adminsWithShifts as $admin ){

			if( !$admin->active ){
				continue;
			}

			$id_admin = $admin->id_admin;
			$admin = Admin::o( $id_admin );

			// get the admin's shifts
			$shifts = Crunchbutton_Community_Shift::q('
				SELECT cs.* FROM admin_shift_assign asa
				INNER JOIN community_shift cs ON asa.id_community_shift = cs.id_community_shift
				WHERE cs.date_start BETWEEN ? AND ?
				AND asa.id_admin = ?
				AND cs.active = true
				ORDER BY date_start ASC
			', [ $now_start, $now_end , $id_admin]);

			if( $shifts->count() > 1 ){
				$hours = [];
				$segments = [];
				$id_community = null;
				$timezone = null;
				foreach( $shifts as $shift ){

					$hours[] = [ 'start' => $shift->dateStart(), 'end' => $shift->dateEnd(), 'startEnd' => $shift->startEndToString() ];
					$id_community = $shift->id_community;
					$timezone = $shift->community()->timezone;
				}
				for( $i = 0; $i < count( $hours ); $i++ ){
					if( $hours[ $i ][ 'merged' ] ){
						continue;
					}
					$next = $i + 1;
					if( $hours[ $next ] ){
						$secs = Crunchbutton_Util::intervalToSeconds( $hours[ $i ][ 'end' ]->diff( $hours[ $next ][ 'start' ] ) );
						$_message = Crunchbutton_Message_Sms::greeting( $admin->firstName() ) . $messagePattern;
						if( $secs > 0 ){
							$message = sprintf( $_message, $now->format( 'M jS Y' ), $hours[ $i ][ 'startEnd' ] );
							Crunchbutton_Community_Shift::shiftMessageWarning( $message, $admin );
						} else {
							$_shift = new Crunchbutton_Community_Shift;
							$_shift->date_start = $hours[ $i ][ 'start' ]->format( 'Y-m-d H:i:s' );
							$_shift->date_end = $hours[ $next ][ 'end' ]->format( 'Y-m-d H:i:s' );
							$_shift->id_community = $id_community;
							$_shift->_timezone = $timezone;
							$message = sprintf( $_message, $now->format( 'M jS Y' ), $_shift->startEndToString() );
							$hours[ $next ][ 'merged' ] = true;
							Crunchbutton_Community_Shift::shiftMessageWarning( $message, $admin );
						}
					} else {
						$message = sprintf( $messagePattern, $now->format( 'M jS Y' ), $hours[ $i ][ 'startEnd' ] );
						Crunchbutton_Community_Shift::shiftMessageWarning( $message, $admin );
					}
				}
			} else if ( $shifts->count() == 1 ){
				$message = sprintf( $messagePattern, $now->format( 'M jS Y' ), $shifts->startEndToString() );
				Crunchbutton_Community_Shift::shiftMessageWarning( $message, $admin );
			}
		}
	}

	public function shiftMessageWarning( $message, $admin ){

		$env = c::getEnv();
		$twilio = new Services_Twilio( c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token );

		$txt = $admin->txt;
		$phone = $admin->phone;

		$num = ( $txt != '' ) ? $txt : $phone;

		echo "Sending sms to ".$num."...\n";

		// #4060 - dont send from driver number
		$rets = Crunchbutton_Message_Sms::send([
			'to' => $num,
			'message' => $message,
			'reason' => Crunchbutton_Message_Sms::REASON_DRIVER_SHIFT
		]);

		foreach ($rets as $ret) {
			if (!$ret->sid) {
				echo 'Error Sending sms to: '.$ret->to;
			}
		}

	}

	public function minutesToStart(){
		$now = new DateTime( 'now', new DateTimeZone( $this->timezone() ) );
		$diff = $now->diff( $this->dateStart() );
		return 	( $diff->i )
					+ ( $diff->h * 60 )
					+ ( $diff->d * 60 * 24 )
					+ ( $diff->m * 60 * 24 * 30 )
					+ ( $diff->y * 60 * 24 * 365 );
	}

	public function shiftWarningWeekly(){

		$weekday = date( 'l' );

		$sendWarning = false;

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );

		switch ( $weekday ) {
			case 'Wednesday':
				$sendWarning = true;
				$dateStart = $now->format( 'Y-m-d' );
				$now->modify( '+ 7 days' );
				$dateEnd = $now->format( 'Y-m-d' );
				$template = "Hi %s! You are scheduled for the following shifts for this schedule cycle: ";
				break;
			case 'Friday':
				$sendWarning = true;
				$now->modify( '+ 1 day' );
				$dateStart = $now->format( 'Y-m-d' );
				$now->modify( '+ 6 days' );
				$dateEnd = $now->format( 'Y-m-d' );
				$template = "Hi %s! Just a reminder of your upcoming shifts at %s: ";
				break;
		}

		if( !$sendWarning || !$dateStart || !$dateEnd ){
			return;
		}

		// Get the shifts of this range
		$drivers = Crunchbutton_Admin::drivers();

		echo '<pre>';
		foreach( $drivers as $driver ){

			if( !$driver->active ){
				continue;
			}

			$communities = $driver->communitiesHeDeliveriesFor();

			foreach( $communities as $community ){

				$message = '';

				$shifts = Crunchbutton_Community_Shift::q( 'SELECT cs.* FROM community_shift cs INNER JOIN admin_shift_assign asa ON asa.id_community_shift = cs.id_community_shift WHERE cs.date_start >= "' . $dateStart . ' 00:00:00" AND cs.date_end <= "' . $dateEnd . ' 23:59:59" AND asa.id_admin = "' . $driver->id_admin . '" AND cs.id_community = "' . $community->id_community . '" ORDER BY cs.date_start ASC' );

				if( $shifts->count() > 0 ){

					$commas = '';

					foreach( $shifts as $shift ){

						if( $shift->isHidden() ){
							continue;
						}

						if( $message == '' ){
							$message = sprintf( $template, $driver->firstName(), $shift->community()->name );
						}
						$message .= $commas;

						if( intval( $shift->dateStart()->format( 'i' ) ) > 0 ){
							$message .= $shift->dateStart()->format( 'D (m/d) g:ia' );
						} else {
							$message .= $shift->dateStart()->format( 'D (m/d) ga' );
						}
						$message .= '-';
						if( intval( $shift->dateEnd()->format( 'i' ) ) > 0 ){
							$message .= $shift->dateEnd()->format( 'g:ia T' );
						} else {
							$message .= $shift->dateEnd()->format( 'ga T' );
						}
						$commas = ', ';
					}

					$message .= '.';
					Log::debug( [ 'action' => $message, 'type' => 'driver-schedule' ] );
					echo $message;
					echo "\n";

					if( $driver->phone ){
						// Send the sms
						Crunchbutton_Message_Sms::send( [ 'to' => $driver->phone, 'message' => $message, 'reason' => Crunchbutton_Message_Sms::REASON_DRIVER_SHIFT ] );
						// Crunchbutton_Support::createNewWarning(  [ 'body' => $message, 'phone' => $driver->phone, 'dont_open_ticket' => true ] );
					}

					// Send the email
					if( $driver->email ){
						$mail = new Cockpit_Email_Driver_Shift( [ 'email' => $driver->email, 'message' => $message ] );
						$mail->send();
					}
				}
			}
		}
	}

	public function pexCardRemoveShiftFunds(){
		$communities = Crunchbutton_Community::q( 'SELECT c.* FROM community c ORDER BY c.name ' );
		foreach( $communities as $community ){
			if( $community->timezone ){
				// remove funds
				$now = new DateTime( 'now', new DateTimeZone( $community->timezone ) );
				$now->modify( '- 120 minutes' );
				$_now = $now->format( 'Y-m-d H:i' );
				$now->modify( '+ 60 minutes' );
				$_interval = $now->format( 'Y-m-d H:i' );
				$nextShifts = Crunchbutton_Community_Shift::q('
					SELECT DISTINCT( cs.id_community_shift ) AS id, cs.* FROM admin_shift_assign asa
					INNER JOIN community_shift cs ON cs.id_community_shift = asa.id_community_shift
					WHERE
						cs.date_end >= ?
						AND cs.date_end <= ?
						AND cs.id_community = ?
				', [$_now, $_interval, $community->id_community]);
				if( $nextShifts->count() > 0 ){
					foreach( $nextShifts as $shift ){
						$assigments = Crunchbutton_Admin_Shift_Assign::q('SELECT * FROM admin_shift_assign asa WHERE id_community_shift = ?', [$shift->id_community_shift]);

						foreach( $assigments as $assignment ){
							$admin = $assignment->admin();
							$pexcard = $admin->pexcard();
							if( $pexcard->id_admin_pexcard ){
								$pexcard->removeFundsShiftFinished( $assigments->id_admin_shift_assign );
							}
						}
					}
				}
			}
		}
	}

	// Return the assigned shifts for the next week
	public function assignedShiftHours( $id_community, $allDay = false ){

		$hours = [];

		$community = Crunchbutton_Community::o( $id_community );

		if( $community->id_community ){

			$now = new DateTime( 'now', new DateTimeZone( $community->timezone ) );
			$now->modify( '+ 15 min' );
			if( $allDay ){
				$from = $now->format( 'Y-m-d' );
			} else {
				$from = $now->format( 'Y-m-d H:i' );
			}
			$now->modify( '+ 7 days' );
			$to = $now->format( 'Y-m-d H:i' );

			$query = '
				SELECT DISTINCT( cs.id_community_shift ) AS id, cs.* FROM admin_shift_assign asa
				INNER JOIN community_shift cs ON cs.id_community_shift = asa.id_community_shift
				WHERE
					cs.date_start >= ?
					AND cs.date_start <= ?
					AND cs.id_community = ?
					ORDER BY cs.date_start ASC
			';

			$nextShifts = Crunchbutton_Community_Shift::q($query, [$from, $to, $community->id_community]);

			foreach( $nextShifts as $shift ){

				$start = $shift->dateStart();
				$end = $shift->dateEnd();

				if (!$start || !$end) {
					continue;
				}

				$day = strtolower( $start->format( 'D' ) );
				$time_open = $start->format( 'H:i' );
				$time_close = $end->format( 'H:i' );

				$new_day = false;

				if( $start->format( 'Ymd' ) < $end->format( 'Ymd' ) && $end->format( 'Hi' ) != '0000' ){
					$new_day = true;
					$new_day_time_open = '00:01';
					$new_day_time_close = $time_close;
					$time_close = '23:59';
				}

				$hours[] = ( object )[ 	'day' => $day,
																'time_open' => $time_open,
																'time_close' => $time_close ];

				if( $new_day ){
					$hours[] = ( object )[ 	'day' => strtolower( $end->format( 'D' ) ),
																	'time_open' => $new_day_time_open,
																	'time_close' => $new_day_time_close ];
				}

			}
		}
		return $hours;
	}

	// Return the shifts for the next week
	public static function shiftsForNextWeek( $id_community, $todayAssigned = false, $justAssignedShifts = false ){

		$hours = [];

		$community = Crunchbutton_Community::o( $id_community );

		if( $community->id_community ){

			$now = new DateTime( 'now', new DateTimeZone( $community->timezone ) );
			$from = $now->format( 'Y-m-d' );
			$now->modify( '+ 7 days' );
			$to = $now->format( 'Y-m-d' );

			$now->modify( '- 7 days' );

			$today = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );

			$query = 'SELECT cs.*, asa.id_admin_shift_assign FROM community_shift cs
									LEFT JOIN admin_shift_assign asa ON asa.id_community_shift = cs.id_community_shift
									WHERE cs.date_start >= ? AND cs.date_start <= ? AND cs.id_community = ? ORDER BY cs.date_start ASC';

			$nextShifts = Crunchbutton_Community_Shift::q($query, [$from, $to, $community->id_community]);

			foreach( $nextShifts as $shift ){

				$start = $shift->dateStart();
				$end = $shift->dateEnd();

				if (!$start || !$end) {
					continue;
				}

				if( $todayAssigned && $start->format( 'Ymd' ) == $today->format( 'Ymd' ) && !$shift->id_admin_shift_assign ){
					continue;
				}

				if( $justAssignedShifts  ){
					if( !$shift->id_admin_shift_assign ){
						continue;
					}
				}

				$day = strtolower( $start->format( 'D' ) );
				$full = $start->format( 'D: m/d/Y' );
				$time_open = $start->format( 'H:i' );
				$time_close = $end->format( 'H:i' );
				$id_admin_shift_assign = $shift->id_admin_shift_assign;

				$new_day = false;

				if( $start->format( 'Ymd' ) < $end->format( 'Ymd' ) && $end->format( 'Hi' ) != '0000' ){
					$new_day = true;
					$new_day_time_open = '00:01';
					$new_day_time_close = $time_close;
					$time_close = '23:59';
				}

				$hours[] = ( object )[ 	'day' => $day,
																'full' => $full,
																'time_open' => $time_open,
																'time_close' => $time_close ];

				if( $new_day ){
					$hours[] = ( object )[ 	'day' => strtolower( $end->format( 'D' ) ),
																	'full' => $full,
																	'time_open' => $new_day_time_open,
																	'time_close' => $new_day_time_close ];
				}

			}
		}
		return $hours;
	}

	public static function currentDriverShift( $id_admin ){
		$admin = Admin::o( $id_admin );
		$now = new DateTime( 'now', $admin->timezone() );
		$now = $now->format( 'Y-m-d H:i:s' );
		$shift = Crunchbutton_Community::q( 'SELECT * FROM community_shift cs
																								INNER JOIN admin_shift_assign asa ON cs.id_community_shift = asa.id_community_shift
																								WHERE
																									cs.date_start <= "' . $now . '"
																								AND
																									cs.date_end >=  "' . $now . '"
																								AND asa.id_admin = ' . $id_admin . ' LIMIT 1' );
		return $shift;
	}

	public function warningDriversBeforeTheirShift(){

		$env = c::getEnv();

		$twilio = new Services_Twilio( c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token );

		$minutes = 15;

		$communities = Crunchbutton_Community::q( 'SELECT DISTINCT( c.id_community ) AS id, c.* FROM community c INNER JOIN restaurant_community rc ON rc.id_community = c.id_community INNER JOIN restaurant r ON r.id_restaurant = rc.id_restaurant WHERE r.active = true AND r.delivery_service = true AND c.id_community != "?" AND ( c.driver_checkin IS NULL OR c.driver_checkin = 0 ) ORDER BY c.name', [ Crunchbutton_Community::CUSTOMER_SERVICE_ID_COMMUNITY ] );

		$messagePattern = 'Your shift starts in %s minutes. Your shift(s) today, %s: %s. If you have any questions/feedback for us, feel free to text us back!';

		foreach( $communities as $community ){

			if( $community->timezone ){

				$now = new DateTime( 'now', new DateTimeZone( $community->timezone ) );
				$now->modify( '- 5 minutes' );
				$_now = $now->format( 'Y-m-d H:i' );
				$now->modify( '+ ' . ( $minutes + 5 ) . ' minutes' );
				$_interval = $now->format( 'Y-m-d H:i' );

				$nextShifts = Crunchbutton_Community_Shift::q('
					SELECT DISTINCT( cs.id_community_shift ) AS id, cs.* FROM admin_shift_assign asa
					INNER JOIN community_shift cs ON cs.id_community_shift = asa.id_community_shift
					WHERE
						cs.date_start >= ?
						AND cs.date_start <= ?
						AND cs.id_community = ?
				', [$_now, $_interval, $community->id_community]);

				if( $nextShifts->count() > 0 ){

					foreach( $nextShifts as $shift ){

						$assigments = Crunchbutton_Admin_Shift_Assign::q( 'SELECT asa.* FROM admin_shift_assign asa INNER JOIN admin a ON a.id_admin = asa.id_admin WHERE asa.id_community_shift = ? AND asa.warned = false AND a.active = true', [$shift->id_community_shift]);

						foreach( $assigments as $assignment ){

							$shift = $assignment->shift();

							if( $shift->isHidden() ){
								continue;
							}

							$admin = $assignment->admin();
							$minutesToStart = $shift->minutesToStart();

							if( $minutesToStart > 0 ){

								// convert to string
								$minutesToStart = "$minutesToStart";

								$date_shift_ends = new DateTime( $shift->dateEnd()->format( 'Y-m-d H:i:s' ), new DateTimeZone( $community->timezone ) );
								$date_shift_ends->modify( '+5 minutes' );

								$nextShift = Crunchbutton_Community_Shift::q('
									SELECT cs.*, asa.id_admin_shift_assign FROM community_shift cs
									INNER JOIN admin_shift_assign asa ON asa.id_community_shift = cs.id_community_shift AND id_admin = ?
									WHERE
										cs.id_community = ?
										AND cs.date_start >= ?
										AND cs.date_start <= ?
									ORDER BY cs.date_start ASC
									LIMIT 1
								', [$admin->id_admin, $shift->id_community, $shift->dateEnd()->format( 'Y-m-d H:i:s' ), $date_shift_ends->format( 'Y-m-d H:i:s' )]);


								if( $nextShift->id_community_shift ){
									$nextShift = $nextShift->get( 0 );
									$secs = Crunchbutton_Util::intervalToSeconds( $nextShift->dateEnd()->diff( $shift->dateStart() ) );
									if( $secs > 0 ){
										$_shift = new Crunchbutton_Community_Shift;
										$_shift->date_start = $shift->dateStart()->format( 'Y-m-d H:i:s' );
										$_shift->date_end = $nextShift->dateEnd()->format( 'Y-m-d H:i:s' );
										$_shift->id_community = $shift->id_community;
										$_shift->_timezone = $shift->timezone;
										$message = sprintf( $messagePattern, $minutesToStart, $now->format( 'M jS Y' ), $_shift->startEndToString() );
										$_assignment = Crunchbutton_Admin_Shift_Assign::o( $nextShift->id_admin_shift_assign );

										if( $_assignment->id_admin_shift_assign ){
											$_assignment->warned = 1;
											$_assignment->save();
										}

									} else {
										$message = sprintf( $messagePattern, $minutesToStart, $now->format( 'M jS Y' ), $shift->startEndToString() );
									}
								} else {
									$message = sprintf( $messagePattern, $minutesToStart, $now->format( 'M jS Y' ), $shift->startEndToString() );
								}
								$txt = $admin->txt;
								$phone = $admin->phone;

								$num = ( $txt != '' ) ? $txt : $phone;


								echo "Sending sms to ".$num."...\n";

								$message = Crunchbutton_Message_Sms::greeting( $admin->firstName() ) . $message;

								Log::debug( [ 'id_admin_shift_assign' => $assignment->id_admin_shift_assign, 'message' => $message, 'type' => 'driver-remind' ] );

								// #4060 - dont send from driver number
								$rets = Crunchbutton_Message_Sms::send([
									'to' => $num,
									'message' => $message,
									'reason' => Crunchbutton_Message_Sms::REASON_DRIVER_SHIFT
								]);
								$assignment->warned = 1;
								$assignment->save();
								foreach ($rets as $ret) {
									if (!$ret->sid) {
										echo 'Error Sending sms to: '.$ret->to;
									}
								}
							}
						}
					}
				}
			}
		}
	}
}
