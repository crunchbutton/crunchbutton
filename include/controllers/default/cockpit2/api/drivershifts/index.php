<?php

class Controller_api_drivershifts extends Crunchbutton_Controller_RestAccount {
	
	public function init() {
		$communities = c::admin()->communitiesHeDeliveriesFor();
		$_communities = [];
		foreach( $communities as $community ){
			$_communities[] = $community->id_community;
		}
		// get the shifts for the next 7 days
 		$shifts = Crunchbutton_Community_Shift::nextShiftsByCommunities( $_communities );
		$export = [];
		foreach ( $shifts as $shift ) {
			$drivers = $shift->getDrivers();
			$mine = 0;
			$_drivers = [];
			foreach ( $drivers as $driver ) { 
				if( $driver->id_admin == c::admin()->id_admin ){
					$mine = 1;
				}
				$_drivers[] = [ 'name' => $driver->name, 'phone' => $driver->phone() ];
			}
			$export[] = Model::toModel( [
					'id_community_shift' => $shift->id_community_shift,
					'community' => $shift->community()->name,
					'date' => [ 'day' => $shift->dateStart()->format( 'D, M jS' ), 'start_end' => $shift->startEndToString(), 'timezone' => $shift->timezoneAbbr() ],
					'drivers' => $_drivers,
					'mine' => $mine
				] );
		}
		echo json_encode( $export );
	}
}