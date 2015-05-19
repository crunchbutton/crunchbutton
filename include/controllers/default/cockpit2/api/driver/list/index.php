<?php

class Controller_api_driver_list extends Crunchbutton_Controller_RestAccount {

	public function init() {

		$hasPermission = ( c::admin()->permission()->check( ['global', 'drivers-all'] ) );
		if( !$hasPermission ){
			$this->_error();
			exit;
		}

		if( c::getPagePiece( 3 ) == 'pexcard' ){
			$this->_pexcard();
		}

		$resultsPerPage = 20;

		if ( c::getPagePiece( 4 ) ) {
			$page = c::getPagePiece( 4 );
		} else {
			$page = 1;
		}

		$drivers = Crunchbutton_Admin::driversList( c::getPagePiece( 5 ) );

		$start = ( ( $page - 1 ) * $resultsPerPage ) + 1;
		$end = $start + $resultsPerPage;
		$count = 1;

		$docs = Cockpit_Driver_Document::driver();

		$list = [];
		foreach( $drivers as $driver ){

			if( $count >= $start && $count < $end ){
				$data = $driver->exports( [ 'permissions', 'groups' ] );
				$data[ 'vehicle' ] = $driver->vehicle();
				$sentAllDocs = true;

				$payment_type = $driver->payment_type();

				foreach( $docs as $doc ){

					if( $doc->id_driver_document == Cockpit_Driver_Document::ID_INDY_CONTRACTOR_AGREEMENT_HOURLY &&
						$payment_type->payment_type != Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS ){
						continue;
					}

					if( $doc->id_driver_document == Cockpit_Driver_Document::ID_INDY_CONTRACTOR_AGREEMENT_ORDER &&
						$payment_type->payment_type == Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS ){
						continue;
					}

					// see: https://github.com/crunchbutton/crunchbutton/issues/3393
					if( $doc->isRequired( $data[ 'vehicle' ] ) ){
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

		$pages = ceil( count( $drivers ) / $resultsPerPage );

		$data = [];
		$data[ 'count' ] = count( $drivers );
		$data[ 'pages' ] = $pages;
		$data[ 'prev' ] = ( $page > 1 ) ? $page - 1 : null;
		$data[ 'page' ] = intval( $page );
		$data[ 'next' ] = ( $page < $pages ) ? $page + 1 : null;
		$data[ 'results' ] = $list;

		echo json_encode( $data );
	}

	private function _pexcard(){
		$out = [];
		$drivers = Crunchbutton_Admin::q( 'SELECT a.name, a.id_admin FROM admin a INNER JOIN admin_payment_type apt ON apt.id_admin = a.id_admin WHERE a.active = true ORDER BY a.name' );
		foreach( $drivers as $drive ){
			$out[] = [ 'id_admin' => intval( $drive->id_admin ), 'name' => $drive->name ];
		}
		echo json_encode( $out );exit;
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}