<?php

class Cockpit_Community_Closed_Log extends Cana_Table {

	// @todo - merge the shift hours

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
		$log = Cockpit_Community_Closed_Log::q( 'SELECT * FROM community_closed_log WHERE day = "' . $day . '" AND id_community = "' . $id_community . '" AND type = "' . $type . '"' );
		if( $log->id_community_closed_log ){
			return true;
		}
		return false;
	}

public function forceCloseHoursLog( $community ){

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$now->modify( '- 10 week' );

		$force_closed_times = Crunchbutton_Community_Changeset::q( 'SELECT ccs.*, cc.field FROM community_change cc
																																	INNER JOIN community_change_set ccs ON ccs.id_community_change_set = cc.id_community_change_set AND id_community = "' . $community->id_community . '"
																																	AND ( cc.field = "close_all_restaurants" OR cc.field = "close_3rd_party_delivery_restaurants" OR cc.field = "is_auto_closed" )
																																	AND cc.new_value = 1
																																	AND DATE( timestamp  ) > "' . $now->format( 'Y-m-d' ) . '"
																																	ORDER BY cc.id_community_change DESC' );
		$out = [];

		$autoClosedAdmin = Admin::login( Crunchbutton_Community::AUTO_SHUTDOWN_COMMUNITY_LOGIN );

		$days = [];

		foreach( $force_closed_times as $force_close ){


			$closed_at = $force_close->date();

			if( $force_close->field == 'close_all_restaurants' ){
				$_type = Cockpit_Community_Closed_Log::TYPE_ALL_RESTAURANTS;
			} else if ( $force_close->field == 'close_3rd_party_delivery_restaurants' ){
				$_type = Cockpit_Community_Closed_Log::TYPE_3RD_PARTY_DELIVERY_RESTAURANTS;
			} else if ( $force_close->field == 'is_auto_closed' ){
				$_type = Cockpit_Community_Closed_Log::TYPE_AUTO_CLOSED;
			}

			$open = $community->_openedAt( $force_close->id_community_change_set, $force_close->field );

			if( $open ){

				$opened_at = $open->date();

				if( $_type == Cockpit_Community_Closed_Log::TYPE_ALL_RESTAURANTS || $_type == Cockpit_Community_Closed_Log::TYPE_3RD_PARTY_DELIVERY_RESTAURANTS ){

					// Check if there was driver working
					$_closed_shift = new DateTime( $closed_at->format( 'Y-m-d H:i:s' ), new DateTimeZone( c::config()->timezone ) );
					$_closed_shift->setTimezone( new DateTimeZone( $community->timezone ) );
					$_opened_shift = new DateTime( $opened_at->format( 'Y-m-d H:i:s' ), new DateTimeZone( c::config()->timezone ) );
					$_opened_shift->setTimezone( new DateTimeZone( $community->timezone ) );

					$query = 'SELECT DISTINCT( cs.id_community_shift ) AS id, cs.* FROM community_shift cs
											INNER JOIN admin_shift_assign asa ON asa.id_community_shift = cs.id_community_shift
											WHERE
											cs.id_community = "' . $community->id_community . '"
											AND
											(
												(
													DATE_FORMAT( cs.date_start, "%Y-%m-%d %H:%i" ) <= "' . $_closed_shift->format( 'Y-m-d H:i' ) . '"
												AND
													DATE_FORMAT( cs.date_end, "%Y-%m-%d %H:%i" ) <= "' . $_opened_shift->format( 'Y-m-d H:i' ) . '"
												AND
													DATE_FORMAT( cs.date_end, "%Y-%m-%d %H:%i" ) >= "' . $_opened_shift->format( 'Y-m-d H:i' ) . '"
												)
											OR
												(
													DATE_FORMAT( cs.date_start, "%Y-%m-%d %H:%i" ) >= "' . $_closed_shift->format( 'Y-m-d H:i' ) . '"
												AND
													DATE_FORMAT( cs.date_end, "%Y-%m-%d %H:%i" ) <= "' . $_opened_shift->format( 'Y-m-d H:i' ) . '"
												)
											OR
												(
													DATE_FORMAT( cs.date_start, "%Y-%m-%d %H:%i" ) <= "' . $_opened_shift->format( 'Y-m-d H:i' ) . '"
												AND
													DATE_FORMAT( cs.date_start, "%Y-%m-%d %H:%i" ) >= "' . $_closed_shift->format( 'Y-m-d H:i' ) . '"
												AND
													DATE_FORMAT( cs.date_end, "%Y-%m-%d %H:%i" ) >= "' . $_opened_shift->format( 'Y-m-d H:i' ) . '"
												)
											OR
												(
													DATE_FORMAT( cs.date_start, "%Y-%m-%d %H:%i" ) <= "' . $_closed_shift->format( 'Y-m-d H:i' ) . '"
												AND
													DATE_FORMAT( cs.date_end, "%Y-%m-%d %H:%i" ) >= "' . $_opened_shift->format( 'Y-m-d H:i' ) . '"
												)
											)
										ORDER BY cs.date_start';

					$shifts = Crunchbutton_Community_Shift::q( $query );
					$hours = 0;
					// Shifts with drivers
					foreach( $shifts as $shift ){

						$shift_start = $shift->dateStart();
						$shift_end = $shift->dateEnd();
						$start = null;
						$end = null;
						$start = ( $shift_start->format( 'YmdHis' ) >= $_closed_shift->format( 'YmdHis' ) ) ? $shift_start : $_closed_shift;
						$end = ( $shift_end->format( 'YmdHis' ) <= $_opened_shift->format( 'YmdHis' ) ) ? $shift_end : $_opened_shift;

						$_output = [];

						$closed_at = $force_close->date();
						$_output[ 'closed_at' ] = $start->format( 'Y-m-d H:i:s' );

						$_output[ 'type' ] = Cockpit_Community_Closed_Log::TYPE_CLOSED_WITH_DRIVER;

						if( $start->format( 'Ymd' ) < $end->format( 'Ymd' ) ){

							$_end = new DateTime( $start->format( 'Y-m-d ' ) . '23:59:59', new DateTimeZone( $community->timezone ) );
							$_output[ 'opened_at' ] = $opened_at->format( 'Y-m-d H:i:s' );
							$interval = $_end->diff( $start );
							$_output[ 'hours' ] = Crunchbutton_Util::interval2Hours( $interval );
							$out[] = $_output;

							$_output = [];
							$_start = new DateTime( $end->format( 'Y-m-d ' ) . '00:00:01', new DateTimeZone( $community->timezone ) );
							$_output[ 'closed_at' ] = $_start->format( 'Y-m-d H:i:s' );
							$_output[ 'type' ] = Cockpit_Community_Closed_Log::TYPE_CLOSED_WITH_DRIVER;
							$interval = $_start->diff( $end );
							$_output[ 'hours' ] = Crunchbutton_Util::interval2Hours( $interval );
							$_output[ 'opened_at' ] = $end->format( 'Y-m-d H:i:s' );
							$out[] = $_output;
						} else {

							$interval = $start->diff( $end );
							$_output[ 'hours' ] = Crunchbutton_Util::interval2Hours( $interval );
							$_output[ 'opened_at' ] = $end->format( 'Y-m-d H:i:s' );
							$out[] = $_output;
						}
					}
				}

				$_closed_shift = new DateTime( $closed_at->format( 'Y-m-d H:i:s' ), new DateTimeZone( c::config()->timezone ) );
				$_closed_shift->setTimezone( new DateTimeZone( $community->timezone ) );
				$_opened_shift = new DateTime( $opened_at->format( 'Y-m-d H:i:s' ), new DateTimeZone( c::config()->timezone ) );
				$_opened_shift->setTimezone( new DateTimeZone( $community->timezone ) );

				$query = 'SELECT DISTINCT( cs.id_community_shift ) AS id, cs.* FROM community_shift cs
											WHERE
											cs.id_community = "' . $community->id_community . '"
											AND
											(
												(
													DATE_FORMAT( cs.date_start, "%Y-%m-%d %H:%i" ) <= "' . $_closed_shift->format( 'Y-m-d H:i' ) . '"
												AND
													DATE_FORMAT( cs.date_end, "%Y-%m-%d %H:%i" ) <= "' . $_opened_shift->format( 'Y-m-d H:i' ) . '"
												AND
													DATE_FORMAT( cs.date_end, "%Y-%m-%d %H:%i" ) >= "' . $_opened_shift->format( 'Y-m-d H:i' ) . '"
												)
											OR
												(
													DATE_FORMAT( cs.date_start, "%Y-%m-%d %H:%i" ) >= "' . $_closed_shift->format( 'Y-m-d H:i' ) . '"
												AND
													DATE_FORMAT( cs.date_end, "%Y-%m-%d %H:%i" ) <= "' . $_opened_shift->format( 'Y-m-d H:i' ) . '"
												)
											OR
												(
													DATE_FORMAT( cs.date_start, "%Y-%m-%d %H:%i" ) <= "' . $_opened_shift->format( 'Y-m-d H:i' ) . '"
												AND
													DATE_FORMAT( cs.date_start, "%Y-%m-%d %H:%i" ) >= "' . $_closed_shift->format( 'Y-m-d H:i' ) . '"
												AND
													DATE_FORMAT( cs.date_end, "%Y-%m-%d %H:%i" ) >= "' . $_opened_shift->format( 'Y-m-d H:i' ) . '"
												)
											OR
												(
													DATE_FORMAT( cs.date_start, "%Y-%m-%d %H:%i" ) <= "' . $_closed_shift->format( 'Y-m-d H:i' ) . '"
												AND
													DATE_FORMAT( cs.date_end, "%Y-%m-%d %H:%i" ) >= "' . $_opened_shift->format( 'Y-m-d H:i' ) . '"
												)
											)
										ORDER BY cs.date_start';

				$shifts = Crunchbutton_Community_Shift::q( $query );

				$hours = 0;

				foreach( $shifts as $shift ){

					$shift_start = $shift->dateStart();
					$shift_end = $shift->dateEnd();
					$start = null;
					$end = null;
					$start = ( $shift_start->format( 'YmdHis' ) >= $_closed_shift->format( 'YmdHis' ) ) ? $shift_start : $_closed_shift;
					$end = ( $shift_end->format( 'YmdHis' ) <= $_opened_shift->format( 'YmdHis' ) ) ? $shift_end : $_opened_shift;

					$_output = [];

					$closed_at = $force_close->date();
					$_output[ 'closed_at' ] = $start->format( 'Y-m-d H:i:s' );

					$_output[ 'type' ] = $_type;

					if( $start->format( 'Ymd' ) < $end->format( 'Ymd' ) ){

						$_end = new DateTime( $start->format( 'Y-m-d ' ) . '23:59:59', new DateTimeZone( $community->timezone ) );
						$_output[ 'opened_at' ] = $opened_at->format( 'Y-m-d H:i:s' );
						$interval = $_end->diff( $start );
						$_output[ 'hours' ] = Crunchbutton_Util::interval2Hours( $interval );
						$out[] = $_output;

						$_output = [];
						$_start = new DateTime( $end->format( 'Y-m-d ' ) . '00:00:01', new DateTimeZone( $community->timezone ) );
						$_output[ 'closed_at' ] = $_start->format( 'Y-m-d H:i:s' );
						$_output[ 'type' ] = Cockpit_Community_Closed_Log::TYPE_CLOSED_WITH_DRIVER;
						$interval = $_start->diff( $end );
						$_output[ 'hours' ] = Crunchbutton_Util::interval2Hours( $interval );
						$_output[ 'opened_at' ] = $end->format( 'Y-m-d H:i:s' );
						$out[] = $_output;
					} else {

						$interval = $start->diff( $end );
						$_output[ 'hours' ] = Crunchbutton_Util::interval2Hours( $interval );
						$_output[ 'opened_at' ] = $end->format( 'Y-m-d H:i:s' );
						$out[] = $_output;
					}
				}
			/*
				if( $opened_at->format( 'Ymd' ) > $closed_at->format( 'Ymd' ) ){
					$_type = $output[ 'type' ];
					$_closed_base = new DateTime( $closed_at->format( 'Y-m-d H:i:s' ), new DateTimeZone( c::config()->timezone ) );
					$_opened_base = new DateTime( $opened_at->format( 'Y-m-d H:i:s' ), new DateTimeZone( c::config()->timezone ) );

					$hours = Crunchbutton_Util::interval2Hours( $_opened_base->diff( $_closed_base ) );

					if( $hours > 24 ){
						while ( $hours > 24 ) {

							$_opened_at = new DateTime( $_closed_base->format( 'Y-m-d ' ) . '23:59:59', new DateTimeZone( c::config()->timezone ) );
							$output[ 'opened_at' ] = $_opened_at->format( 'Y-m-d H:i:s' );
							$interval = $_opened_at->diff( $closed_at );
							$output[ 'hours' ] = ( Crunchbutton_Util::interval2Hours( $interval ) > 24 ? 24 : Crunchbutton_Util::interval2Hours( $interval ) );

							$out[] = $output;
							$output = [];

							$_closed_base->modify( '+ 1 day' );

							$_closed_at = new DateTime( $_closed_base->format( 'Y-m-d ' ) . '00:00:01', new DateTimeZone( c::config()->timezone ) );
							$output[ 'closed_at' ] = $_closed_at->format( 'Y-m-d H:i:s' );
							$output[ 'type' ] = $_type;

							$hours = Crunchbutton_Util::interval2Hours( $_opened_base->diff( $_closed_base ) );

							if( $hours > 24 ){

								$_opened_at = new DateTime( $_closed_at->format( 'Y-m-d ' ) . '23:59:59', new DateTimeZone( c::config()->timezone ) );
								$output[ 'opened_at' ] = $_opened_at->format( 'Y-m-d H:i:s' );
								$interval = $_opened_at->diff( $_closed_at );
								$output[ 'hours' ] = Crunchbutton_Util::interval2Hours( $interval );
								$out[] = $output;

								$_closed_at = new DateTime( $_opened_at->format( 'Y-m-d ' ) . '00:00:01', new DateTimeZone( c::config()->timezone ) );
								$_closed_at->modify( '+ 1 day' );
								$output[ 'closed_at' ] = $_closed_at->format( 'Y-m-d H:i:s' );
								$output[ 'type' ] = $_type;

								$_closed_base->modify( '+ 1 day' );

							} else {

								$output[ 'opened_at' ] = $_opened_base->format( 'Y-m-d H:i:s' );
								$interval = $opened_at->diff( $_closed_at );
								$output[ 'hours' ] = Crunchbutton_Util::interval2Hours( $interval );
								$out[] = $output;
							}
						}

					} else {
						$_opened_at = new DateTime( $closed_at->format( 'Y-m-d ' ) . '23:59:59', new DateTimeZone( c::config()->timezone ) );
						$output[ 'opened_at' ] = $_opened_at->format( 'Y-m-d H:i:s' );
						$interval = $_opened_at->diff( $closed_at );
						$output[ 'hours' ] = Crunchbutton_Util::interval2Hours( $interval );
						$out[] = $output;
						$output = [];
						$_closed_at = new DateTime( $_opened_at->format( 'Y-m-d ' ) . '00:00:01', new DateTimeZone( c::config()->timezone ) );
						$_closed_at->modify( '+ 1 day' );
						$output[ 'closed_at' ] = $_closed_at->format( 'Y-m-d H:i:s' );
						$output[ 'type' ] = $_type;
						$output[ 'opened_at' ] = $opened_at->format( 'Y-m-d H:i:s' );
						$interval = $opened_at->diff( $_closed_at );
						$output[ 'hours' ] = Crunchbutton_Util::interval2Hours( $interval );
						$out[] = $output;
					}
				} else {
					$output[ 'opened_at' ] = $opened_at->format( 'Y-m-d H:i:s' );
					$interval = $opened_at->diff( $closed_at );
					$output[ 'hours' ] = Crunchbutton_Util::interval2Hours( $interval );
					$out[] = $output;
				}
				*/
			}
		}

		$_out = [];
		foreach( $out as $segment ){
			$date = new DateTime( $segment[ 'opened_at' ], new DateTimeZone( c::config()->timezone ) );
			$date = $date->format( 'Y-m-d' );
			$type = $segment[ 'type' ];
			if( !isset( $_out[ $date ] ) ){
				$_out[ $date ] = [ 	'id_community' => $community->id_community,
														'day' => $date,
														Cockpit_Community_Closed_Log::TYPE_ALL_RESTAURANTS => 0,
														Cockpit_Community_Closed_Log::TYPE_3RD_PARTY_DELIVERY_RESTAURANTS => 0,
														Cockpit_Community_Closed_Log::TYPE_CLOSED_WITH_DRIVER => 0,
														Cockpit_Community_Closed_Log::TYPE_AUTO_CLOSED => 0,
														Cockpit_Community_Closed_Log::TYPE_TOTAL => 0 ];
			}
			$_out[ $date ][ $type ] += $segment[ 'hours' ];

			if( $segment[ 'type' ] != Cockpit_Community_Closed_Log::TYPE_CLOSED_WITH_DRIVER ){
				$_out[ $date ][ 'total' ] += $segment[ 'hours' ];
			}
		}
		return $_out;
	}

	public function save_log(){
		$out = [];
		$communities = Crunchbutton_Community::q( 'SELECT * FROM community' );
		foreach( $communities as $community ){
			$_out = Cockpit_Community_Closed_Log::forceCloseHoursLog( $community );
			$community = [ $community->id_community => $_out ];
			if( count( $_out ) ){
				$out = array_merge( $out, $community );
			}
		}

		foreach( $out as $community ){
			foreach( $community as $day ){
				foreach( [ 	Cockpit_Community_Closed_Log::TYPE_ALL_RESTAURANTS,
										Cockpit_Community_Closed_Log::TYPE_3RD_PARTY_DELIVERY_RESTAURANTS,
										Cockpit_Community_Closed_Log::TYPE_AUTO_CLOSED,
										Cockpit_Community_Closed_Log::TYPE_CLOSED_WITH_DRIVER,
										Cockpit_Community_Closed_Log::TYPE_TOTAL ] as $type ){
					$hours_closed = floatval( $day[ $type ] );
					if( $hours_closed ){
						if( Cockpit_Community_Closed_Log::checkIfLogAlreadyExists( $day[ 'day' ], $day[ 'id_community' ], $type ) == false ){
							$log = new Cockpit_Community_Closed_Log;
							$log->id_community = $day[ 'id_community' ];
							$log->day = $day[ 'day' ];
							$log->hours_closed = $hours_closed;
							$log->type = $type;
							$log->save();
						}
					}
				}
			}
		}
	}

}