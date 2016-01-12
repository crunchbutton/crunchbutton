<?php

class Controller_api_driver_shifts extends Crunchbutton_Controller_RestAccount {

	public function init() {

		switch ( c::getPagePiece( 3 ) ) {
			case 'schedule':
				$this->_schedule();
				break;

			case 'rating':
				$this->_rating();
				break;

			case 'checkin':
				$this->_checkin();
				break;

			default:
				$this->_list();
				break;
		}
	}

	private function _communities(){
		$communities = c::user()->communitiesHeDeliveriesFor();
		$_communities = [];
		foreach( $communities as $community ){
			$_communities[] = $community->id_community;
		}
		return $_communities;
	}

	private function _checkin(){
		$id_admin_shift_assign = $this->request()[ 'id_admin_shift_assign' ];
		if( $id_admin_shift_assign ){
			$assignment = Crunchbutton_Admin_Shift_Assign::o( $id_admin_shift_assign );
			$id_admin = c::user()->id_admin;
			Crunchbutton_Admin_Shift_Assign_Confirmation::confirm( $assignment, false, $id_admin );
			echo json_encode( [ 'success' => true ] );exit;
		}
		echo json_encode( [ 'error' => true ] );exit;
	}

	private function _rating(){
		if( $this->method() == 'post' ){
			$this->_ratingAction();
		} else {
			$this->_ratingList();
		}
	}

	private function _schedule(){
		if( $this->method() == 'post' ){
			$this->_scheduleAction();
		} else {
			$this->_scheduleList();
		}
	}

	private function _scheduleAction(){

		$id_admin = c::user()->id_admin;

		$action = trim( $this->request()[ 'action' ] );

		switch ( $action ) {

			case 'save':
				$shifts = $this->request()[ 'shifts' ];
				foreach ( $shifts as $shift => $ranking ) {
					$preference = Crunchbutton_Admin_Shift_Preference::q( 'SELECT * FROM admin_shift_preference WHERE id_community_shift = ? AND id_admin = ?', [$shift, $id_admin]);
					if( !$preference->id_community_shift ){
						$preference = new Crunchbutton_Admin_Shift_Preference;
						$preference->id_community_shift = $shift;
						$preference->id_admin = $id_admin;
					}
					$preference->ranking = $ranking;
					$preference->save();
				}
				$this->_scheduleList();
				break;

			case 'shiftsAvailableToWork':
				$shifts = $this->request()[ 'shifts' ];
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

				$id_admin = c::user()->id_admin;
				$status = Crunchbutton_Admin_Shift_Status::getByAdminWeekYear( $id_admin, $week, $year );
				$status->shifts = $shifts;
				$status->date = date( 'Y-m-d H:i:s' );
				$status->save();
				$this->_scheduleList();
				exit;
				break;

			case 'rankingChange':
				$id_community_shift = $this->request()[ 'id_community_shift' ];
				$id_community_shift_change = $this->request()[ 'id_community_shift_change' ];
				$preference = Crunchbutton_Admin_Shift_Preference::q( 'SELECT * FROM admin_shift_preference WHERE id_community_shift = ? AND id_admin = ?', [$id_community_shift, $id_admin]);
				if( $preference->id_community_shift ){
					$change = Crunchbutton_Admin_Shift_Preference::q( 'SELECT * FROM admin_shift_preference WHERE id_community_shift = ? AND id_admin = ?', [$id_community_shift_change, $id_admin]);
					if( $change->id_community_shift ){
						$change_ranking = $change->ranking;
						$change->ranking = $preference->ranking;
						$change->save();

						$preference->ranking = $change_ranking;
						$preference->save();
					}

				}
				$this->_scheduleList();
				exit;
				break;

			case 'wantToWork':
				$id_community_shift = $this->request()[ 'id_community_shift' ];
				$ranking = $this->request()[ 'ranking' ];
				$preference = Crunchbutton_Admin_Shift_Preference::q( 'SELECT * FROM admin_shift_preference WHERE id_community_shift = ? AND id_admin = ?', [$id_community_shift, $id_admin]);
				if( !$preference->id_community_shift ){
					$preference = new Crunchbutton_Admin_Shift_Preference;
					$preference->id_community_shift = $id_community_shift;
					$preference->id_admin = $id_admin;
				}
				$preference->ranking = $ranking;
				$preference->save();
				$this->_scheduleList();
				exit;
				break;

			case 'dontWantToWork':
				$id_community_shift = $this->request()[ 'id_community_shift' ];
				$preference = Crunchbutton_Admin_Shift_Preference::q( 'SELECT * FROM admin_shift_preference WHERE id_community_shift = ? AND id_admin = ?', [$id_community_shift, $id_admin]);
				if( !$preference->id_community_shift ){
					$preference = new Crunchbutton_Admin_Shift_Preference;
					$preference->id_community_shift = $id_community_shift;
					$preference->id_admin = $id_admin;
				}
				$preference->ranking = 0;
				$preference->save();
				$this->_scheduleList();
				exit;
				break;

			default:
				$this->_scheduleList();
				break;
		}

	}

	private function _ratingAction(){
		$id_admin = c::user()->id_admin;
		$action = trim( $this->request()[ 'action' ] );
		if( $action == 'save' ){
			$shifts = $this->request()[ 'shifts' ];
			foreach ( $shifts as $shift => $ranking ) {
				$preference = Crunchbutton_Admin_Shift_Preference::q( 'SELECT * FROM admin_shift_preference WHERE id_community_shift = ? AND id_admin = ?', [$shift, $id_admin]);
				if( !$preference->id_community_shift ){
					$preference = new Crunchbutton_Admin_Shift_Preference;
					$preference->id_community_shift = $shift;
					$preference->id_admin = $id_admin;
				}
				$preference->ranking = $ranking;
				$preference->save();
			}
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
			$options = $this->request()[ 'options' ];
			$status = Crunchbutton_Admin_Shift_Status::getByAdminWeekYear( $id_admin, $week, $year );
			$status->shifts_from = $options[ 'shifts_from' ];
			$status->shifts_to = $options[ 'shifts_to' ];
			$status->save();

			$this->_ratingList();
		}
	}

	private function _ratingList(){

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

		$shifts = [];

		$id_admin = c::user()->id_admin;

		$from = $days[ 0 ];
		$to = $days[ 6 ];

		$preferences = Crunchbutton_Admin_Shift_Preference::shiftsByPeriod( $id_admin, $from->format( 'Y-m-d' ), $to->format( 'Y-m-d' ) );
		$prefs = [];
		foreach ( $preferences as $preference ) {
			$prefs[ $preference->id_community_shift ] = intval( $preference->ranking );
		}

		$communities = $this->_communities();
		foreach( $communities as $community ) {
			$_community = Crunchbutton_Community::o( $community );
			foreach( $days as $day ){
				$segments = Crunchbutton_Community_Shift::shiftByCommunityDay( $community, $day->format( 'Y-m-d' ) );
				foreach ( $segments as $segment ) {
					if( $segment->isHidden() ){
						continue;
					}
					$export = $segment->export();
					$data = array( 'id_community_shift' => $segment->id_community_shift, 'day' => $export[ 'period' ][ 'day_start' ] . ' - ' . $export[ 'period' ][ 'weekday' ] , 'period' => $export[ 'period' ][ 'toString' ], 'tz' => $export[ 'period' ][ 'timezone_abbr' ], 'community' => $_community->name );
					$data[ 'assigned' ] = Crunchbutton_Admin_Shift_Assign::adminHasShift( $id_admin, $segment->id_community_shift );
					if( $prefs[ $segment->id_community_shift ] ){
						$data[ 'ranking' ] = $prefs[ $segment->id_community_shift ];
					} else {
						$data[ 'ranking' ] = -1;
					}
					$shifts[] = $data;
				}
			}
		}

		$_availableShifts = 0;
		if( $shifts && count( $shifts ) > 0 ){
			$res_array = [];
			foreach( $shifts as $shift ){
				if( !$shift[ 'ranking' ] || $shift[ 'ranking' ] > 0 ){
					$_availableShifts++;
				}
			}
		}

		$status = Crunchbutton_Admin_Shift_Status::getByAdminWeekYear( $id_admin, $week, $year );
		// See #6768
		if( $_availableShifts === 0 ){
			$status->completed = 1;
		}
		$status->save();
		$status = Crunchbutton_Admin_Shift_Status::getByAdminWeekYear( $id_admin, $week, $year );

		if( !$status->shifts_from && $status->shifts_from != 0 ){
			$status->shifts_from = 0;
		}
		if( !$status->shifts_to && $status->shifts_to != 0 ){
			$status->shifts_to = 0;
		}

		echo json_encode( [ 'info' => [ 'period' => $shifts_period ], 'completed' => $status->completed, 'shifts' => $status->shifts, 'options' => [ 'shifts_to' => $status->shifts_to, 'shifts_from' => $status->shifts_from ], 'results' => $shifts ] );
	}

	private function _scheduleList(){

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

		$id_admin = c::user()->id_admin;

		$from = $days[ 0 ];
		$to = $days[ 6 ];

		$preferences = Crunchbutton_Admin_Shift_Preference::shiftsByPeriod( $id_admin, $from->format( 'Y-m-d' ), $to->format( 'Y-m-d' ) );
		$ranking = 1;
		$rankings = [];
		foreach ( $preferences as $preference ) {
			$wantToWork[ $preference->id_community_shift ] = $ranking;
			$rankings[ $ranking ] = $preference->id_community_shift;
			$ranking++;
		}

		$preferences = Crunchbutton_Admin_Shift_Preference::shiftsByPeriod( $id_admin, $from->format( 'Y-m-d' ), $to->format( 'Y-m-d' ), true );
		foreach ( $preferences as $preference ) {
			$donWantToWork[ $preference->id_community_shift ] = true;;
		}

		$communities = $this->_communities();
		foreach( $communities as $community ) {
			$_community = Crunchbutton_Community::o( $community );
			foreach( $days as $day ){
				$segments = Crunchbutton_Community_Shift::shiftByCommunityDay( $community, $day->format( 'Y-m-d' ) );
				foreach ( $segments as $segment ) {
					if( $segment->isHidden() ){
						continue;
					}
					$export = $segment->export();
					$data = array( 'id_community_shift' => $segment->id_community_shift, 'day' => $export[ 'period' ][ 'day_start' ] . ' - ' . $export[ 'period' ][ 'weekday' ] , 'period' => $export[ 'period' ][ 'toString' ], 'tz' => $export[ 'period' ][ 'timezone_abbr' ], 'community' => $_community->name );
					if( $wantToWork[ $segment->id_community_shift ] ){
						$data[ 'assigned' ] = Crunchbutton_Admin_Shift_Assign::adminHasShift( $id_admin, $segment->id_community_shift );
						$data[ 'ranking' ] = $wantToWork[ $segment->id_community_shift ];
						$data[ 'ranking_prev' ] = ( $rankings[ $data[ 'ranking' ]  - 1 ] ? $rankings[ $data[ 'ranking' ]  - 1 ] : 0 );
						$data[ 'ranking_next' ] = ( $rankings[ $data[ 'ranking' ]  + 1 ] ? $rankings[ $data[ 'ranking' ]  + 1 ] : 0 );
					} else if( $donWantToWork[ $segment->id_community_shift ] ){
						$data[ 'ranking' ] = 0;
					}
					$_shifts[] = $data;
				}
			}
		}

		$shifts = [];
		$_availableShifts = 0;
		if( $_shifts && count( $_shifts ) > 0 ){
			$res_array = [];
			foreach( $_shifts as $shift ){
				if( $shift[ 'ranking' ] && $shift[ 'ranking' ] > 0 ){
					$index = $shift[ 'ranking' ];
				} else {
					if( !isset( $shift[ 'ranking' ] ) ){
						$_availableShifts++;
					}
					$index = $ranking;
					$ranking++;
				}
				$res_array[ $index ] = $shift;
			}

			ksort( $res_array );

			foreach( $res_array as $shift ){
				$shifts[] = $shift;
			}
		}

		$status = Crunchbutton_Admin_Shift_Status::getByAdminWeekYear( $id_admin, $week, $year );
		// See #6768
		if( $_availableShifts === 0 ){
			$status->completed = 1;
		}

		$status->save();

		echo json_encode( [ 'info' => [ 'period' => $shifts_period ], 'completed' => $status->completed, 'shifts' => $status->shifts, 'results' => $shifts ] );
	}

	private function _list(){

		// this method returns the shifts for the next 7 days
		$shifts = Crunchbutton_Community_Shift::nextShiftsByCommunities( $this->_communities() );
		$export = [];

		foreach ( $shifts as $shift ) {

			$showShift = true;

			if( $shift->isHidden() ){
				$showShift = false;
			}

			$mine = 0;
			$_drivers = [];
			$drivers = $shift->getDrivers();
			foreach ( $drivers as $driver ) {
				$addDriver = true;
				if( $shift->isHidden() ){
					$addDriver = false;
				}
				if( c::admin()->id_admin == $driver->id_admin ){
					$addDriver = true;
					$showShift = true;
				}
				if( $addDriver ){
					$_drivers[] = [ 'name' => $driver->name, 'phone' => $driver->phone(), 'id' => $driver->id_admin];
				}
			}

			if( !$showShift ){
				continue;
			}

			$export[] = Cana_Model::toModel( [
					'id_community_shift' => $shift->id_community_shift,
					'community' => $shift->community()->name,
					'date' => [ 'day' => $shift->dateStart()->format( 'D, M jS' ), 'start_end' => $shift->startEndToString(), 'timezone' => $shift->timezoneAbbr() ],
					'drivers' => $_drivers
				] );
		}
		echo json_encode( $export );
	}

}
