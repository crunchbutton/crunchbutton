<?php

class Cockpit_Community_Closed_Log extends Cana_Table {

	const TYPE_ALL_RESTAURANTS = 'all_restaurants';
	const TYPE_3RD_PARTY_DELIVERY_RESTAURANTS = 'close_3rd_party_delivery_restaurants';
	const TYPE_AUTO_CLOSED = 'auto_closed';
	const TYPE_TOTAL = 'total';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('community_closed_log')
			->idVar('id_community_closed_log')
			->load($id);
	}

	public function checkIfLogAlreadyExists( $day, $id_community, $type ){
		$log = Cockpit_Community_Closed_Log::q( 'SELECT *
																								FROM community_closed_log
																								WHERE
																									day = "' . $day . '"
																									AND
																										id_community = "' . $id_community . '"
																									AND
																										type = "' . $type . '"' );
		if( $log->id_community_closed_log ){
			return true;
		}
		return false;
	}

public function forceCloseHoursLog( $community ){

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );

		$force_closed_times = Crunchbutton_Community_Changeset::q( 'SELECT ccs.*, cc.field FROM community_change cc
																																	INNER JOIN community_change_set ccs ON ccs.id_community_change_set = cc.id_community_change_set AND id_community = "' . $community->id_community . '"
																																	AND ( cc.field = "close_all_restaurants" OR cc.field = "close_3rd_party_delivery_restaurants" OR cc.field = "is_auto_closed" )
																																	AND cc.new_value = 1
																																	AND DATE( timestamp  ) < "' . $now->format( 'Y-m-d' ) . '"
																																	ORDER BY cc.id_community_change DESC' );
		$out = [];

		$autoClosedAdmin = Admin::login( Crunchbutton_Community::AUTO_SHUTDOWN_COMMUNITY_LOGIN );

		$days = [];

		foreach( $force_closed_times as $force_close ){

			$output = [];

			$closed_at = $force_close->date();
			$output[ 'closed_at' ] = $closed_at->format( 'Y-m-d H:i:s' );
			$output[ 'closed_at_id' ] = $force_close->id_community_change_set;

			if( $force_close->field == 'close_all_restaurants' ){
				$output[ 'type' ] = Cockpit_Community_Closed_Log::TYPE_ALL_RESTAURANTS;
			} else if ( $force_close->field == 'close_3rd_party_delivery_restaurants' ){
				$output[ 'type' ] = Cockpit_Community_Closed_Log::TYPE_3RD_PARTY_DELIVERY_RESTAURANTS;
			} else if ( $force_close->field == 'is_auto_closed' ){
				$output[ 'type' ] = Cockpit_Community_Closed_Log::TYPE_AUTO_CLOSED;
			}

			$open = $community->_openedAt( $force_close->id_community_change_set, $force_close->field );
			if( $open ){
				$opened_at = $open->date();

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
					$output[ 'opened_at_id' ] = $open->id_community_change_set;
					$interval = $opened_at->diff( $closed_at );
					$output[ 'hours' ] = Crunchbutton_Util::interval2Hours( $interval );
					$out[] = $output;
				}
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
														Cockpit_Community_Closed_Log::TYPE_AUTO_CLOSED => 0,
														Cockpit_Community_Closed_Log::TYPE_TOTAL => 0 ];
			}
			$_out[ $date ][ $type ] += $segment[ 'hours' ];
			$_out[ $date ][ 'total' ] += $segment[ 'hours' ];
			if( $_out[ $date ][ $type ] > 24 ){
				$_out[ $date ][ $type ] = 24;
			}
			if( $_out[ $date ][ 'total' ] > 24 ){
				$_out[ $date ][ 'total' ] = 24;
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
										Cockpit_Community_Closed_Log::TYPE_TOTAL ] as $type ){
					$hours_closed = intval( $day[ $type ] );
					// $hours_closed = $day[ $type ];
					if( $hours_closed ){
						if( !Cockpit_Community_Closed_Log::checkIfLogAlreadyExists( $day[ 'day' ], $day[ 'id_community' ], $type ) ){
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