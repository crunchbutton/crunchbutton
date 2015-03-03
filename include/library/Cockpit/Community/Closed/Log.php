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

	public function save_log(){

$log = new Cockpit_Community_Closed_Log;
							$log->id_community = 1;
							$log->day = '2015-02-27';
							$log->hours_closed = 15;
							$log->type = 'close_3rd_party_delivery_restaurants';
							$log->save();
		die('hard');

		$out = [];
		$communities = Crunchbutton_Community::q( 'SELECT * FROM community' );
		foreach( $communities as $community ){
			$_out = $community->forceCloseHoursLog();
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
					if( $day[ $type ] ){
						// if( !Cockpit_Community_Closed_Log::checkIfLogAlreadyExists( $day[ 'day' ], $day[ 'id_community' ], $type ) ){
							$log = new Cockpit_Community_Closed_Log;
							$log->id_community = $day[ 'id_community' ];
							$log->day = $day[ 'day' ];
							$log->hours_closed = $day[ $type ];
							$log->type = $type;
							echo '<pre>';var_dump( $log );exit();
							// $log->save();
							die('hard');
						// }
					}
				}
			}
		}
	}

}