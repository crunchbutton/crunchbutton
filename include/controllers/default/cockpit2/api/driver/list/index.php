<?php

class Controller_api_driver_list extends Crunchbutton_Controller_RestAccount {
	
	public function init() {	

		$resultsPerPage = 20;

		if ( c::getPagePiece( 3 ) ) {
			$page = c::getPagePiece( 3 );
		} else {
			$page = 1;
		}

		$drivers = Crunchbutton_Admin::driversList( c::getPagePiece( 4 ) );

		$start = ( ( $page - 1 ) * $resultsPerPage ) + 1;
		$end = $start + $resultsPerPage;
		$count = 1;

		$list = [];
		foreach( $drivers as $driver ){
			if( $count >= $start && $count < $end ){
				$data = $driver->exports( [ 'permissions', 'groups' ] );
				$actions = Cockpit_Driver_Log::lastAction( $driver->id_admin );
				if( $actions ){
					$data[ 'actions' ] = [ 'last' => $actions, 'next' => Cockpit_Driver_Log::nextAction( $driver->id_admin ) ];
				} else {
					$data[ 'actions' ] = null;
				}
				$list[] = $data;
			}
			$count++;
		}

		$pages = ceil( $drivers->count() / $resultsPerPage );

		$data = [];
		$data[ 'count' ] = $drivers->count();
		$data[ 'pages' ] = $pages;
		$data[ 'prev' ] = ( $page > 1 ) ? $page - 1 : null;
		$data[ 'page' ] = intval( $page );
		$data[ 'next' ] = ( $page < $pages ) ? $page + 1 : null;
		$data[ 'results' ] = $list;

		echo json_encode( $data );
	}
}