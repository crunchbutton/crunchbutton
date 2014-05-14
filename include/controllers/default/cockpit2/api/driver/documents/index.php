<?php

class Controller_api_driver_documents extends Crunchbutton_Controller_RestAccount {
	
	public function init() {

		switch ( c::getPagePiece( 3 ) ) {

			case 'upload':
				if( $_FILES ){
					$ext = pathinfo( $_FILES['file']['name'], PATHINFO_EXTENSION );
					if( Util::allowedExtensionUpload( $ext ) ){
						$name = pathinfo( $_FILES['file']['name'], PATHINFO_FILENAME );
						$name = str_replace( $ext, '', $name );
						$random = substr( str_replace( '.' , '', uniqid( rand(), true ) ), 0, 8 );
						$name = Util::slugify( $random . '-' . $name );
						$name = substr( $name, 0, 40 ) . '.'. $ext;
						$file = Crunchbutton_Driver_Document_Status::path() . $name;
						if ( copy( $_FILES[ 'file' ][ 'tmp_name' ], $file ) ) {
							chmod( $file, 0777 );
						}
						echo json_encode( ['success' => $name ] );
						exit;
					} else {
						$this->_error( 'invalid extension' );	
					}
				} else {
					$this->_error();
				}
				break;
			
			case 'save':
				$id_admin = $this->request()[ 'id_admin' ];
				$id_driver_document = $this->request()[ 'id_driver_document' ];
				if( $id_admin && $id_driver_document ){
					$docStatus = Crunchbutton_Driver_Document_Status::document( $id_admin, $id_driver_document );
					if( !$docStatus->id_driver_document_status ){
						$docStatus->id_admin = $id_admin;
						$docStatus->id_driver_document = $id_driver_document;
					}
					// todo: delete old doc
					$docStatus->datetime = date('Y-m-d H:i:s');
					$docStatus->file = $this->request()[ 'file' ];
					$docStatus->save();
					echo '<pre>';var_dump( $docStatus->exports() );exit();
					echo json_encode( ['success' => 'success'] );	
				} else {
					$this->_error();
				}
				break;
			default:
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
				break;
		}
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}