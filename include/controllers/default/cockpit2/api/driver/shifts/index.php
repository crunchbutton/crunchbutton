<?php

class Controller_api_driver_shifts extends Crunchbutton_Controller_RestAccount {
	
	public function init() {

		switch ( c::getPagePiece( 3 ) ) {
			case 'schedule':
				$this->_schedule();
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

			case 'rankingChange':
				$id_community_shift = $this->request()[ 'id_community_shift' ];
				$id_community_shift_change = $this->request()[ 'id_community_shift_change' ];
				$preference = Crunchbutton_Admin_Shift_Preference::q( 'SELECT * FROM admin_shift_preference WHERE id_community_shift = "' . $id_community_shift . '" AND id_admin = "' . $id_admin . '"' );
				if( $preference->id_community_shift ){
					$change = Crunchbutton_Admin_Shift_Preference::q( 'SELECT * FROM admin_shift_preference WHERE id_community_shift = "' . $id_community_shift_change . '" AND id_admin = "' . $id_admin . '"' );	
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
				$preference = Crunchbutton_Admin_Shift_Preference::q( 'SELECT * FROM admin_shift_preference WHERE id_community_shift = "' . $id_community_shift . '" AND id_admin = "' . $id_admin . '"' );
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
				$preference = Crunchbutton_Admin_Shift_Preference::q( 'SELECT * FROM admin_shift_preference WHERE id_community_shift = "' . $id_community_shift . '" AND id_admin = "' . $id_admin . '"' );
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

	private function _scheduleList(){

		$year = date( 'Y', strtotime( '- 1 day' ) );
		$week = date( 'W', strtotime( '- 1 day' ) );

		$firstDay = new DateTime( date( 'Y-m-d', strtotime( $year . 'W' . $week . 1 ) ), new DateTimeZone( c::config()->timezone  ) );
		$shifts_period = 'From ' . $firstDay->format( 'M jS Y' );
		$firstDay->modify( '+ 1 week' );
		$shifts_period .= ' to ' . $firstDay->format( 'M jS Y' );

		$firstDay->modify( '- 1 week' );

		if( date( 'l' ) == 'Monday' ){
			$firstDay->modify( '+ 2 week' );	
		} else {
			$firstDay->modify( '+ 1 week' );
		}

		// todo: remove it -> get the current week
		$firstDay->modify( '- 1 week' );
		
		$week = $firstDay->format( 'W' );
		$year = $firstDay->format( 'Y' );

		$days = [];
		for( $i = 0; $i <= 6; $i++ ){
			$days[] = new DateTime( $firstDay->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
			$firstDay->modify( '+ 1 day' );
		}

		$wantToWork = [];
		$donWantToWork = [];

		$id_admin = c::user()->id_admin;

		$from = new DateTime( $days[ 0 ]->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
		$to = new DateTime( $days[ 6 ]->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );

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
			foreach( $days as $day ){ 
				$segments = Crunchbutton_Community_Shift::shiftByCommunityDay( $community, $day->format( 'Y-m-d' ) );
				foreach ( $segments as $segment ) {
					$export = $segment->export();
					$data = array( 'id_community_shift' => $segment->id_community_shift, 'day' => $export[ 'period' ][ 'day_start' ], 'period' => $export[ 'period' ][ 'toString' ], 'tz' => $export[ 'period' ][ 'timezone_abbr' ] );
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

		$res_array = array();

		foreach( $_shifts as $shift ){
			if( $shift[ 'ranking' ] && $shift[ 'ranking' ] > 0 ){
				$index = $shift[ 'ranking' ];
			} else {
				$index = $ranking;
				$ranking++;
			}
			$res_array[ $index ] = $shift;
		}

		ksort( $res_array );

		$shifts = [];
		foreach( $res_array as $shift ){
			$shifts[] = $shift;
		}

		echo json_encode( [ 'info' => [ 'period' => $shifts_period ], 'results' => $shifts ] );
	}

	private function _list(){
		
		// this method returns the shifts for the next 7 days
		$shifts = Crunchbutton_Community_Shift::nextShiftsByCommunities( $this->_communities() );
		$export = [];

		foreach ( $shifts as $shift ) {
			$drivers = $shift->getDrivers();
			$mine = 0;
			$_drivers = [];
			foreach ( $drivers as $driver ) { 
				$_drivers[] = [ 'name' => $driver->name, 'phone' => $driver->phone(), 'id' => $driver->id_admin];
			}
			$export[] = Model::toModel( [
					'id_community_shift' => $shift->id_community_shift,
					'community' => $shift->community()->name,
					'date' => [ 'day' => $shift->dateStart()->format( 'D, M jS' ), 'start_end' => $shift->startEndToString(), 'timezone' => $shift->timezoneAbbr() ],
					'drivers' => $_drivers
				] );
		}
		echo json_encode( $export );
	}

}