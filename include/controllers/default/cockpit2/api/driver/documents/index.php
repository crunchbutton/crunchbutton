<?php

class Controller_api_driver_documents extends Crunchbutton_Controller_RestAccount {
	
	public function init() {

		$id_admin = false;
		if( c::getPagePiece( 3 ) ){
			$admin = Crunchbutton_Admin::o( c::getPagePiece( 3 ) );
			if( $admin->id_admin ){
				$id_admin = $admin->id_admin;
			}
		}

		// shows the regular list
		$list = [];
		$docs = Crunchbutton_Driver_Document::all();
		foreach( $docs as $doc ){
			$out = $doc->exports();;
			// echo '<pre>';var_dump( $out );exit();
			if( $id_admin ){
				$docStatus = Crunchbutton_Driver_Document_Status::document( $id_admin, $doc->id_driver_document );	
				if( $docStatus->id_driver_document_status ){
					$out[ 'status' ] = $docStatus->exports();
				}
			}
			
			$list[] = $out;
		}
		echo json_encode( $list );
	}

}