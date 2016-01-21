<?php

class Controller_api_shifts extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if( !c::admin()->permission()->check( ['global', 'support-all', 'support-view', 'support-crud' ] ) ){
			$this->error( 401 );
		}

		switch ( c::getPagePiece( 2 ) ) {
			case 'shifts':
				$this->_loadShifts();
				break;
			case 'shift':
				$this->_loadShift();
				break;
			case 'log':
				$this->_log();
				break;
			case 'week-start':
				$this->_weekStart();
				break;
			case 'communities-shift':
				$this->_communitiesWithShift();
				break;
			case 'show-hide-shift':
				$this->_showHideShift();
				break;
			case 'add-shift':
				$this->_addShift();
				break;
			case 'edit-shift':
				$this->_editShift();
				break;
			case 'assign-driver':
				$this->_assignDriver();
				break;
			case 'save-driver-note':
				$this->_saveDriverNote();
				break;
			case 'save-driver-note':
				$this->_saveDriverNote();
				break;
			case 'update-removal-info':
				$this->_updateRemovalInfo();
				break;
			case 'remove-recurring-shift':
				$this->_removeRecurringShift();
				break;
		}
	}

	private function _updateRemovalInfo(){
		if( $this->method() == 'post' ){
			$log = Crunchbutton_Admin_Shift_Assign_Log::o( $this->request()[ 'id_admin_shift_assign_log' ] );
			if( $log->id_admin_shift_assign_log ){
				$log->reason = $this->request()[ 'reason' ];
				$log->reason_other = $this->request()[ 'reason_other' ];
				$log->find_replacement = ( $this->request()[ 'find_replacement' ] == 'true' ? true : false );
				$log->id_admin = c::user()->id_admin;
				$log->save();
				echo json_encode( [ 'success' => true ] );exit;
			}
		}
		$this->error( 404 );
	}

	private function _removeShift(){
		if( $this->method() == 'post' ){
			$id_community_shift = $this->request()[ 'id_community_shift' ];
			if( $id_community_shift ){
				Crunchbutton_Community_Shift::remove( $id_community_shift );
			}
			echo json_encode( [ 'success' => true ] );exit;
		}
		$this->error( 404 );
	}

	private function _removeRecurringShift(){
		if( $this->method() == 'post' ){
			$id_community_shift = $this->request()[ 'id_community_shift' ];
			if( $id_community_shift ){
				Crunchbutton_Community_Shift::removeRecurring( $id_community_shift );
			}
			echo json_encode( [ 'success' => true ] );exit;
		}
		$this->error( 404 );
	}

	private function _saveDriverNote(){
		if( $this->method() == 'post' ){
			$id_admin = $this->request()[ 'id_admin' ];
			$notes = $this->request()[ 'notes' ];
			$driver = Admin::o( $id_admin );
			if( $driver->id_admin ){
				$driver->addNote( $notes );
			}
			echo json_encode( [ 'success' => true ] );exit;
		}
		$this->error( 404 );
	}

	private function _assignDriver(){

		if( $this->method() == 'post' ){

			$id_community_shift = $this->request()[ 'id_community_shift' ];
			$id_admin = $this->request()[ 'id_admin' ];
			$permanent = $this->request()[ 'permanent' ];
			$assigned = $this->request()[ 'assigned' ];

			// assign shift or change the permanency
			if( $assigned ){
				if( !Crunchbutton_Admin_Shift_Assign::adminHasShift( $id_admin, $id_community_shift ) ){
					$assignment = Crunchbutton_Admin_Shift_Assign::assignAdminToShift( $id_admin, $id_community_shift, $permanent );
				}
				if( !$assignment->id_admin_shift_assign ){
					$assignment = Crunchbutton_Admin_Shift_Assign::q( "SELECT * FROM admin_shift_assign WHERE id_admin = ? AND id_community_shift = ? LIMIT 1", [ $id_admin, $id_community_shift ] )->get( 0 );
				}

				$shift = Crunchbutton_Community_Shift::o( $assignment->id_community_shift );
				if( $permanent ){
					// add permanency
					if( !$assignment->isPermanent() ){
						Crunchbutton_Admin_Shift_Assign_Permanently::addDriver( $id_admin, $id_community_shift );
					}
				} else {
					// remove permanency
					if( $assignment->isPermanent() ){
						Crunchbutton_Admin_Shift_Assign_Permanently_Removed::add( $shift->id_community_shift, $id_admin );
						$this->_removePermanency( $shift, $id_admin );
					}
				}
			// remove assinment
			} else {
				$assignment = Crunchbutton_Admin_Shift_Assign::q( "SELECT * FROM admin_shift_assign WHERE id_admin = ? AND id_community_shift = ? LIMIT 1", [ $id_admin, $id_community_shift ] )->get( 0 );
				if( $assignment->id_admin_shift_assign ){
					$shift = Crunchbutton_Community_Shift::o( $assignment->id_community_shift );
					// prevent the shift to be added again
					Crunchbutton_Admin_Shift_Assign_Permanently_Removed::add( $shift->id_community_shift, $id_admin );
					// remove permanency
					if( $assignment->isPermanent() ){
						$this->_removePermanency( $shift, $id_admin );
					}
					$params = [];
					if( $this->request()[ 'reason' ] ){
						$params[ 'reason' ] = $this->request()[ 'reason' ];
					}
					if( $this->request()[ 'reason_other' ] ){
						$params[ 'reason_other' ] = $this->request()[ 'reason_other' ];
					}
					if( $this->request()[ 'find_replacement' ] ){
						$params[ 'find_replacement' ] = ( $this->request()[ 'find_replacement' ] == 'true' ? true : false );
					}
					$assignment->delete( $params );
				}
			}
			echo json_encode( [ 'success' => true ] );exit;
		}
		$this->error( 404 );
	}

	private function _removePermanency( $shift, $id_admin ){

		$id_father = $shift->id_community_shift_father;
		if( !$id_father && $shift->recurring ){
			$id_father = $shift->id_community_shift;
		}

		if( $id_father ){

			$id_admin = $id_admin;
			$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );

			// Remove permanency
			Crunchbutton_Admin_Shift_Assign_Permanently::removeByAdminShiftFather( $id_admin, $id_father );

			// Remove next shifts assignments for the permanency
			$remove_assignment_after = ( $now->format( 'YmdHis' ) > $shift->dateEnd()->format( 'YmdHis' ) ? $now : $shift->dateEnd() ) ;

			$assignments = Crunchbutton_Admin_Shift_Assign::q('
				SELECT asa.* FROM admin_shift_assign asa
				INNER JOIN community_shift cs
					ON cs.id_community_shift = asa.id_community_shift
					AND cs.id_community_shift_father = ?
				WHERE cs.date_start > ?
					AND asa.id_admin = ?
					AND asa.warned = false
			', [$id_father, $remove_assignment_after->format( 'Y-m-d' ), $id_admin]);

			foreach( $assignments as $assignment ){
				$assignment->delete();
			}
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
				$date_base = DateTime::createFromFormat( 'Y-m-d H:i:s', $year . '-' . $month . '-' . $day . ' 00:00:00', new DateTimeZone( $timezone ) );
				$date_base->modify( '- 1 day' );
				for( $i = 0; $i < 7; $i++ ){
					$date_base->modify( '+ 1 day' );
					$_hours = Crunchbutton_Admin_Hour::segmentToDate( $date_base, $segments, $timezone );
					if( $_hours ){
						$hours[] = [ 'start' => $_hours[ 'start' ], 'end' => $_hours[ 'end' ] ];
					}
				}
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

	private function _editShift(){

		if( $this->method() == 'post' ){

			$id_community_shift = $this->request()[ 'id_community_shift' ];
			$segments = $this->request()[ 'period' ];
			$change = $this->request()[ 'change' ];

			switch ( $change ) {
				case 'only-this-shift':
					$shift = Community_Shift::o( $id_community_shift );
					if( $shift->id_community_shift ){
						$shift->updateHours( $segments );
						echo json_encode( [ 'success' => $shift->id_community ] );exit;
					}
					break;

				case 'all-recurring-shifts':

					$shift = Community_Shift::o( $id_community_shift );
					if( $shift->id_community_shift ){

						// update the current shift
						$shift->updateHours( $segments );

						$id_father = $shift->recurringId();
						$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
						$community = $shift->community();
						$now->setTimezone( new DateTimeZone( $community->timezone ) );

						// get all the future recurrent events already created
						$futureShifts = Crunchbutton_Community_Shift::q('SELECT * FROM community_shift WHERE id_community_shift_father = ? AND date_start >= ?', [$id_father, $now->format( 'Y-m-d' ) ]);
						foreach( $futureShifts as $futureShift ){
							$futureShift->updateHours( $segments );
						}
						// update the father
						$father = Community_Shift::o( $id_father );
						$father->updateHours( $segments );
						echo json_encode( [ 'success' => $shift->id_community ] );exit;
					}
					break;
			}


		} else {
			$this->error( 404 );
		}
	}

	private function _log(){
		$shift = Community_Shift::o( c::getPagePiece( 3 ) );
		if( $shift->id_community_shift ){
			echo json_encode( $this->_shiftLog( $shift->id_community_shift ) );exit;
		} else {
			$this->error( 404 );
		}
	}

	private function _loadShift(){

		$shift = Community_Shift::o( c::getPagePiece( 3 ) );

		if( $shift->id_community_shift ){

			$out = [ 'id_community_shift' => $shift->id_community_shift ];
			$community = $shift->community();

			$out[ 'community' ] = [ 'name' => $community->name, 'id_community' => $community->id_community, 'tz' => $community->timezone ];
			$out[ 'segment' ] = [ 'start' => [ 'day' => $shift->dateStart()->format( 'M jS Y l - ' ), 'hour' => $shift->dateStart()->format( 'g:i A' ) ],
														'end' => [ 'day' => $shift->dateEnd()->format( 'M jS Y - l ' ), 'hour' => $shift->dateEnd()->format( 'g:i A' ) ] ];
			$out[ 'hidden' ] = $shift->isHidden();
			$out[ 'recurring' ] = $shift->isRecurring();
			$out[ 'period' ] = $shift->startEndToString();

			$_48hours = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
			$_48hours->setTimezone( new DateTimeZone( $community->timezone ) );
			$_48hours->modify( '+ 48 hours' );

			if( $_48hours->format( 'YmdHis' ) >= $shift->dateStart()->format( 'YmdHis' ) ){
				$out[ 'ask_reason' ] = true;
			}

			$drivers = $shift->community()->getDriversOfCommunity();

			$week = $shift->week();
			$year = $shift->year();

			$out[ 'drivers' ] = [];

			$shift_date = new DateTime( $shift->dateStart()->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );

			if( $shift_date->format( 'l' ) == 'Thursday' ){
				$thursday = $shift_date;
			} else {
				$shift_date->modify( 'last thursday' );
				$thursday = $shift_date;
			}

			$firstDayOfWeek = $thursday->format( 'Y-m-d' );
			$thursday->modify( '+ 6 days' );
			$lastDayOfWeek = $thursday->format( 'Y-m-d' );

			$ranking = [];
			$preferences = $shift->getAdminPreferences();
			foreach( $preferences as $preference ){
				$highestRanking = $preference->highestRankingByPeriod( $preference->id_admin, $firstDayOfWeek, $lastDayOfWeek );
				$ranking[ $preference->id_admin ] = -1;
				if( isset( $preference->ranking ) ){
					$ranking[ $preference->id_admin ] = intval( $preference->ranking );
				}
			}

			$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
			$out[ 'editable' ] = true;

			if( intval( $shift->dateStart()->format( 'Ymd' ) ) <= intval( date( 'Ymd' ) ) ){
				$out[ 'shift_remove_permanency' ] = true;
			}

			$_drivers_assigned = [];

			$_drivers = [];

			foreach( $drivers as $driver ){

				$totalShifts = Admin_Shift_Status::getByAdminWeekYear( $driver->id_admin, $week, $year )->get( 0 );
				if( $totalShifts->shifts_from || $totalShifts->shifts_to ){
					if( $totalShifts->shifts_to == 100 ){
						$totalShifts->shifts_to = 'As many as possible!';
					}
					if( $totalShifts->shifts_from && $totalShifts->shifts_to ){
						$totalShifts = $totalShifts->shifts_from . '-' . $totalShifts->shifts_to;
					} else if( $totalShifts->shifts_from ){
						$totalShifts = $totalShifts->shifts_from;
					} else if( $totalShifts->shifts_to ){
						$totalShifts = $totalShifts->shifts_to;
					}

				} else {
					$totalShifts = '?';
				}

				$_driver = [ 'id_admin' => $driver->id_admin, 'name' => $driver->name, 'phone' => $driver->phone() ];
				$prefs = Crunchbutton_Admin_Shift_Preference::shiftsByPeriod( $driver->id_admin, $firstDayOfWeek, $lastDayOfWeek );
				Crunchbutton_Admin_Shift_Status::getByAdminWeekYear( $driver->id_admin, $week, $year );
				$driverShifts = Crunchbutton_Admin_Shift_Assign::shiftsByAdminPeriod( $driver->id_admin, $firstDayOfWeek, $lastDayOfWeek );
				$_driver[ 'total_shifts' ] = $driverShifts->count();
				$_driver[ 'total_shifts_want_work' ] = $totalShifts;
				$_driver[ 'assigned' ] = ( Crunchbutton_Admin_Shift_Assign::adminHasShift( $driver->id_admin, $shift->id_community_shift ) ) ? true : false;
				$_driver[ 'assigned_permanently' ] = ( Crunchbutton_Admin_Shift_Assign_Permanently::adminIsPermanently( $driver->id_admin, $shift->id_community_shift ) ) ? true : false;

				if( !$_driver[ 'assigned' ] ){
					$assignmentLog = Crunchbutton_Admin_Shift_Assign_Log::logRemovedByShiftDriver( $shift->id_community_shift, $driver->id_admin );
					if( $assignmentLog && $assignmentLog->id_admin_shift_assign_log ){
						$_driver[ 'id_admin_shift_assign_log' ] = $assignmentLog->id_admin_shift_assign_log;
						$_driver[ 'reason' ] = $assignmentLog->reason;
						$_driver[ 'reason_other' ] = $assignmentLog->reason_other;
						if( $assignmentLog->find_replacement ){
							$_driver[ 'find_replacement' ] = 'true';
						} else {
							$_driver[ 'find_replacement' ] = 'false';
						}
					}
				}

				if( $_driver[ 'assigned' ] ){
					$_drivers_assigned[] = $driver->name;
				}

				$_driver[ 'ranking' ] = -1;
				if( isset( $ranking[ $driver->id_admin ] ) ){
					$_driver[ 'ranking' ] = $ranking[ $driver->id_admin ];
				}

				$note = $driver->note();
				$note_data = [];
				if( $note->id_admin_note ){
					$_driver[ 'notes' ] = $note->exports();
					$_driver[ 'notes_text' ] = $_driver[ 'notes' ][ 'text' ];
				} else {
					$_driver[ 'notes_text' ] = '';
				}

				if( $_driver[ 'ranking' ] == 0 ){
					$_driver[ 'sort' ] = -10;
				} else if( $_driver[ 'ranking' ] == -1 ){
					$_driver[ 'sort' ] = 0;
				} else if( $_driver[ 'ranking' ] == 1 ){
					$_driver[ 'sort' ] = 10;
				} else {
					$_driver[ 'sort' ] = 5;
					$_driver[ 'ranking' ] = 2;
				}
				$_drivers[] = $_driver;
			}

			if( count( $_drivers_assigned ) ){
				$_drivers_assigned = join( $_drivers_assigned, ', ' );
				$out[ 'drivers_assigned' ] = $_drivers_assigned;
			}

			if( !$this->request()[ 'ignore_log' ] ){
				$out[ 'log' ] = $this->_shiftLog( $shift->id_community_shift );
			}

			usort( $_drivers, function( $a, $b ) {
				if( $a[ 'sort' ] == $b[ 'sort' ] ){
					return $a[ 'name' ] > $b[ 'name' ];
				}
				return $a[ 'sort' ] < $b[ 'sort' ];
			} );

			$out[ 'drivers' ] = $_drivers;

			echo json_encode( $out );exit;
		} else {
			$this->error( 404 );
		}
	}

	private function _shiftLog( $id_community_shift ){
		$out = [];
		$logs = Crunchbutton_Admin_Shift_Assign_Log::logByShift( $id_community_shift );
		if( $logs ){
			foreach( $logs as $log ){
				$log = $log->exports();
				unset( $log[ 'id' ] );
				unset( $log[ 'id_admin_shift_assign_log' ] );
				unset( $log[ 'id_community_shift' ] );
				unset( $log[ 'id_driver' ] );
				unset( $log[ 'id_admin' ] );

				if( $log[ 'assigned' ] ){
					unset( $log[ 'reason' ] );
					unset( $log[ 'reason_other' ] );
					unset( $log[ 'find_replacement' ] );
				} else {
					if( !$log[ 'reason' ] )	{
						unset( $log[ 'reason' ] );
						unset( $log[ 'reason_other' ] );
						unset( $log[ 'find_replacement' ] );
					} else {
						if( $log[ 'reason' ] == 'Our decision' ){
							unset( $log[ 'find_replacement' ] );
						}
						if( !$log[ 'reason_other' ] ){
							unset( $log[ 'reason_other' ] );
						}
					}
				}


				$out[] = $log;
			}
		}
		return $out;
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

	private function _communitiesWithShift(){
		$out = [];
		$communities = Community_Shift::communitiesWithShift();
		foreach( $communities as $community ){
			$out[] = $community->id_community;
		}
		echo json_encode( $out );
	}

	private function _weekStart(){
		$day = $this->_startDayCurrentWeek();
		$range = [ 'start' => $day->format( 'Y,m,d' ) ];
		echo json_encode( $range );
	}

	private function _startDayCurrentWeek(){
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone  ) );
		$now->setTimezone( new DateTimeZone( Crunchbutton_Community_Shift::CB_TIMEZONE ) );
		$now->setTime( 0, 0, 0 );
		return $now;
	}

	private function _loadShifts(){

		$start = $this->request()['start'];
		$communities = $this->request()['communities'];

		$today = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );

		if( $start && $communities ){

			$currentStartDay = $this->_startDayCurrentWeek();
			$currentEndDay = $this->_startDayCurrentWeek();
			$currentEndDay->modify( '+ 6 days' );
			$currentEndDay->setTime( 23, 59, 59 );

			$out = [ 'days' => [] ];
			$start = explode( 'T', $start );
			$start = new DateTime( $start[ 0 ] . ' 00:00:00', new DateTimeZone( c::config()->timezone  ) );

			if( $start >= $currentStartDay && $start <= $currentEndDay ){
				$current_week = true;
			} else {
				$current_week = false;
			}

			$filterCommunities = $communities;

			$year = ( $this->request()['year'] ? $this->request()['year'] : $start->format( 'Y' ) );
			$month = ( $this->request()['month'] ? $this->request()['month'] : $start->format( 'm' ) );
			$day = ( $this->request()['day'] ? $this->request()['day'] : $start->format( 'd' ) );

			if( $year == $start->format( 'Y' ) && $month == $start->format( 'm' ) && $day == $start->format( 'd' ) ){
				$current = true;
			} else {
				$current = false;
			}

			$firstDay = new DateTime( $year . '-' . $month . '-' . $day, new DateTimeZone( c::config()->timezone  ) );

			$days = [];
			for( $i = 0; $i <= 6; $i++ ){
				$isToday = ( $today->format( 'Ymd' ) == $firstDay->format( 'Ymd' ) );
				$days[] = new DateTime( $firstDay->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
				$out[ 'days' ][ $firstDay->format( 'Ymd' ) ] = [ 'date' => $firstDay->format( 'M jS' ), 'weekday' => $firstDay->format( 'l' ), 'today' => $isToday ];
				$firstDay->modify( '+ 1 day' );
			}

			$communities = [];

			$now = new DateTime( 'now', new DateTimeZone( Crunchbutton_Community_Shift::CB_TIMEZONE ) );

			foreach( $filterCommunities as $community ) {
				$community = Community::permalink( $community );
				if( $community->id_community ){
					$shifts = [];
					foreach( $days as $day ) {
						$editable = true;
						$shifts[ $day->format( 'Ymd' ) ] = [ 'shifts' => [], 'editable' => $editable, 'date' => [ 'day' => $day->format( 'Y-m-d' ), 'formatted' => $day->format( 'M jS Y' ), 'tz' => $community->timezone ] ];
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
			$out[ 'current_week' ] = $current_week;
			$out[ 'now' ] = $_now;
			$out[ 'communities' ] = $communities;

			echo json_encode( $out );exit;
		}
		$this->error( 404 );
	}

	private function _parseSegment( $segment ){
		$out = [	'id_community_shift' => $segment->id_community_shift,
							'period' => $segment->startEndToString(),
							'tz' => $segment->timezoneAbbr(),
							'period_pst' => $segment->startEndToString( Community_Shift::CB_TIMEZONE ) ];

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