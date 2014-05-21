<?php

class Controller_api_driver_list extends Crunchbutton_Controller_RestAccount {
	
	public function init() {	

		$hasPermission = ( c::admin()->permission()->check( ['global', 'drivers-all'] ) );
		if( !$hasPermission ){
			$this->_error();
			exit;
		}

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

		$docs = Cockpit_Driver_Document::all();

		$list = [];
		foreach( $drivers as $driver ){
			if( $count >= $start && $count < $end ){
				$data = $driver->exports( [ 'permissions', 'groups' ] );
				$sentAllDocs = true;
				foreach( $docs as $doc ){
					if( $doc->required ){
						$docStatus = Cockpit_Driver_Document_Status::document( $driver->id_admin, $doc->id_driver_document );	
						if( !$docStatus->id_driver_document_status ){
							$sentAllDocs = false;
						}	
					}
				}
				$data[ 'sent_all_docs' ] = $sentAllDocs;
				
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

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}