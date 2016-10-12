<?php

class Controller_api_driver_documents extends Crunchbutton_Controller_RestAccount {

	public function init() {

		switch ( c::getPagePiece( 3 ) ) {

			case 'download':

				$id_driver_document_status = c::getPagePiece(4);
				$document = Cockpit_Driver_Document_Status::o($id_driver_document_status);

				if (!$document->id_driver_document_status) {
					$this->_error( 'download:file-not-found' );
					exit;
				}

				if (!c::admin()->permission()->check( ['global', 'drivers-all'] ) && $document->id_admin != c::user()->id_admin) {
					$this->_error( 'download:permission-denied' );
					exit;
				}

				$name = $document->driver()->name . ' - ' .$document->driver_document()->name;
				$ext = pathinfo( $document->file, PATHINFO_EXTENSION );
				$name .= '.' . $ext;

				$file = $document->getFile();

				if (!$file) {
					$this->_error( 'download:file-not-found' );
					exit;
				}

				header( 'Content-Description: File Transfer' );
				header( 'Content-Type: application/octet-stream' );
				header( 'Content-Disposition: attachment; filename=' . $name );
				header( 'Expires: 0' );
				header( 'Cache-Control: must-revalidate' );
				header( 'Pragma: public' );
				header( 'Content-Length: ' . filesize( $file ) );
				readfile( $file );
				exit;

				break;

			case 'list':
				$this->_list();
				break;

			case 'list-marketing':
				$this->_listMarketing();
				break;

			case 'remove':
				$this->_remove();
				break;

			case 'approve':
				$this->_approve();
				break;

			case 's3all':
				$docs = Cockpit_Driver_Document_Status::q('select * from driver_document_status');
				foreach ($docs as $doc) {

					if (file_exists(Cockpit_Driver_Document_Status::path().$doc->file)) {
						$r = Cockpit_Driver_Document_Status::toS3(Cockpit_Driver_Document_Status::path().$doc->file, $doc->file);
						//$r = $doc->localToS3();
						print_r($r);
					}


				}
				break;

			case 'upload':

				if( $_FILES ){

					$ext = pathinfo( $_FILES['file']['name'], PATHINFO_EXTENSION );

					if( Util::allowedExtensionUpload( $ext ) ){

						// check the permission
						$id_admin = $_POST[ 'id_admin' ];
						$user = c::user();
						$hasPermission = ( c::admin()->permission()->check( ['global', 'drivers-all'] ) || ( $id_admin == $user->id_admin ) );
						if( !$hasPermission ){
							$this->_error();
							exit;
						}

						$id_driver_document = $_POST[ 'id_driver_document' ];

						if( $id_admin && $id_driver_document ){
							$docStatus = Cockpit_Driver_Document_Status::document( $id_admin, $id_driver_document );
							if( !$docStatus->id_driver_document_status ){
								$docStatus->id_admin = $id_admin;
								$docStatus->id_driver_document = $id_driver_document;
							} else {
								$oldFile = Cockpit_Driver_Document_Status::path() . $docStatus->file;
								if( file_exists( $oldFile ) ){
									@unlink( $oldFile );
								}
							}
							$docStatus->datetime = date('Y-m-d H:i:s');
							$docStatus->save();

							$file = $_FILES['file']['tmp_name'];
							$file_name = $_FILES['file']['name'];

							$docStatus->file = $docStatus->s3Base( $file_name . '.' . $ext );
							// $docStatus->id_driver_document_status . '.'. $ext;
							$docStatus->save();

							$r = Cockpit_Driver_Document_Status::toS3($file, $docStatus->file);

							// save driver's log
							$log = new Cockpit_Driver_Log();
							$log->id_admin = $id_admin;
							$log->action = Cockpit_Driver_Log::ACTION_DOCUMENT_SENT;
							$log->info = $docStatus->id_driver_document . ': ' . $docStatus->file;
							$log->datetime = date('Y-m-d H:i:s');
							$log->save();

							Log::debug( [ 'action' => 'file saved success', 'id_driver_document' => $id_driver_document, 'type' => 'drivers-onboarding'] );

							echo json_encode( ['success' => 'success'] );
						} else {
							$this->_error();
						}

						exit;
					} else {
						$this->_error( 'invalid extension' );
					}
				} else {
					$this->_error();
				}
				break;

			case 'save':
				// deprecated
				return;
				// check the permission
				$id_admin = $this->request()[ 'id_admin' ];
				$user = c::user();
				$hasPermission = ( c::admin()->permission()->check( ['global', 'drivers-all'] ) || ( $id_admin == $user->id_admin ) );
				if( !$hasPermission ){
					$this->_error();
					exit;
				}

				$id_driver_document = $this->request()[ 'id_driver_document' ];

				if( $id_admin && $id_driver_document ){
					$docStatus = Cockpit_Driver_Document_Status::document( $id_admin, $id_driver_document );
					if( !$docStatus->id_driver_document_status ){
						$docStatus->id_admin = $id_admin;
						$docStatus->id_driver_document = $id_driver_document;
					} else {
						$oldFile = Cockpit_Driver_Document_Status::path() . $docStatus->file;
						if( file_exists( $oldFile ) ){
							@unlink( $oldFile );
						}
					}

					$docStatus->datetime = date('Y-m-d H:i:s');
					$docStatus->file = $this->request()[ 'file' ];
					$docStatus->save();

					$docStatus->rename();
					$docStatus = Cockpit_Driver_Document_Status::o( $docStatus->id_driver_document_status );

					// save driver's log
					$log = new Cockpit_Driver_Log();
					$log->id_admin = $id_admin;
					$log->action = Cockpit_Driver_Log::ACTION_DOCUMENT_SENT;
					$log->info = $docStatus->id_driver_document . ': ' . $docStatus->file;
					$log->datetime = date('Y-m-d H:i:s');
					$log->save();

					Log::debug( [ 'action' => 'file saved success', 'id_driver_document' => $id_driver_document, 'type' => 'drivers-onboarding'] );

					echo json_encode( ['success' => 'success'] );
				} else {
					$this->_error();
				}
				break;

			case 'pendency':

					$admin = Crunchbutton_Admin::login( c::getPagePiece( 4 ) );

					if( !$admin->id_admin ){
						$admin = Crunchbutton_Admin::o( c::getPagePiece( 4 ) );
					}

					if( !$admin->id_admin ){
						echo $this->_error();
					}

					$payment_type = $admin->payment_type();

					$user = c::user();
					$hasPermission = ( c::admin()->permission()->check( ['global', 'drivers-all'] ) || ( $admin->id_admin == $user->id_admin ) || c::user()->isCommunityDirector() );
					if( !$hasPermission ){
						echo $this->_error();
					}

					$needToSendDocs = false;
					$docs = Cockpit_Driver_Document::driver();
					foreach( $docs as $doc ){
						if( $doc->required ){

							if( $doc->id_driver_document == Cockpit_Driver_Document::ID_INDY_CONTRACTOR_AGREEMENT_HOURLY &&
								$payment_type->payment_type != Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS ){
								continue;
							}

							if( $doc->id_driver_document == Cockpit_Driver_Document::ID_INDY_CONTRACTOR_AGREEMENT_ORDER &&
								$payment_type->payment_type == Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS ){
								continue;
							}

							$docStatus = Cockpit_Driver_Document_Status::document( $admin->id_admin, $doc->id_driver_document );
							if( !$docStatus->id_driver_document_status ){
								$needToSendDocs = true;
							}
						}
					}
					echo json_encode( [ 'needToSendDocs' => $needToSendDocs ] );
				break;

			case 'marketing-rep':
				$id_admin = false;

				if( c::getPagePiece( 4 ) ){
					$driver = Cockpit_Admin::o( c::getPagePiece( 4 ) );
					if( $driver->id_admin ){
						$id_admin = $driver->id_admin;
					}
				}
				if( !$driver->id_admin ){
					$this->_error();
				}

				// Check if the logged user has permission to see the admin's docs
				$user = c::user();
				$hasPermission = ( c::user()->permission()->check( [ 'global', 'drivers-all' ] ) || ( $id_admin == $user->id_admin ) );

				$payment_type = $driver->payment_type();

				// shows the regular list
				$list = [];
				$docs = Cockpit_Driver_Document::marketing_rep();
				foreach( $docs as $doc ){

					if( $driver && $driver->isCampusManager() && $doc->id_driver_document == Cockpit_Driver_Document::ID_INDY_CONTRACTOR_AGREEMENT_BRAND_REP ){
						continue;
					}

					if( $driver && !$driver->isCampusManager() &&
						( $doc->id_driver_document == Cockpit_Driver_Document::ID_INDY_CONTRACTOR_AGREEMENT_COMMUNITY_MANAGER ||
						$doc->id_driver_document == Cockpit_Driver_Document::ID_COMMUNITY_MANAGER_W9 ) ){
						continue;
					}

					$out = $doc->exports();;
					if( $id_admin && $hasPermission ){
						$docStatus = Cockpit_Driver_Document_Status::document( $id_admin, $doc->id_driver_document );
						if( $docStatus->id_driver_document_status ){
							$approved = $docStatus->admin_approved();
							$out[ 'status' ] = $docStatus->exports();
							if( $driver->id_admin ){
								$out[ 'status' ][ 'approved' ] = $approved->name;
							} else {
								$out[ 'status' ][ 'approved' ] = false;
							}

						}
					}
					$list[] = $out;
				}
				echo json_encode( $list );
				break;

			default:

				$id_admin = false;
				if( c::getPagePiece( 3 ) ){
					$admin = Crunchbutton_Admin::o( c::getPagePiece( 3 ) );
					if( $admin->id_admin ){
						$id_admin = $admin->id_admin;
					}
				}
				if( !$admin->id_admin ){
					$this->_error();
				}

				// Check if the logged user has permission to see the admin's docs
				$user = c::user();
				$hasPermission = ( c::admin()->permission()->check( ['global', 'drivers-all'] ) || ( $id_admin == $user->id_admin ) );

				// get driver's vehicle
				$vehicle = $admin->vehicle();

				$payment_type = $admin->payment_type();

				// shows the regular list
				$list = [];
				$docs = Cockpit_Driver_Document::driver();
				foreach( $docs as $doc ){
					if( !$doc->showDocument( $vehicle ) ){
						continue;
					}

					if( $doc->id_driver_document == Cockpit_Driver_Document::ID_INDY_CONTRACTOR_AGREEMENT_HOURLY &&
						$payment_type->payment_type != Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS ){
						continue;
					}

					if( $doc->id_driver_document == Cockpit_Driver_Document::ID_INDY_CONTRACTOR_AGREEMENT_ORDER &&
						$payment_type->payment_type == Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS ){
						continue;
					}

					$out = $doc->exports();;
					if( $id_admin && $hasPermission ){
						$docStatus = Cockpit_Driver_Document_Status::document( $id_admin, $doc->id_driver_document );
						if( $docStatus->id_driver_document_status ){
							$admin = $docStatus->admin_approved();
							$out[ 'status' ] = $docStatus->exports();
							if( $admin->id_admin ){
								$out[ 'status' ][ 'approved' ] = $admin->name;
							} else {
								$out[ 'status' ][ 'approved' ] = false;
							}

						}
					}
					$list[] = $out;
				}
				echo json_encode( $list );
				break;
		}
	}

	private function _remove(){
		$doc = Cockpit_Driver_Document_Status::o( c::getPagePiece( 4 ) );
		if( $doc->id_driver_document_status ){
			$doc->delete();
			echo json_encode( [ 'success' => true ] );exit();
		} else {
			$this->_error();
		}
	}

	private function _approve(){
		$doc = Cockpit_Driver_Document_Status::o( c::getPagePiece( 4 ) );
		if( $doc->id_driver_document_status ){
			$action = c::getPagePiece( 5 );
			if( $action == 'disapprove' ){
				$doc->id_admin_approved = null;
			} else {
				$doc->id_admin_approved = c::user()->id_admin;
			}
			$doc->save();
			echo json_encode( [ 'success' => true ] );exit();
		} else {
			$this->_error();
		}
	}

	private function _list(){
		$hasPermission = ( c::admin()->permission()->check( ['global', 'drivers-all'] ) );
		if( !$hasPermission ){
			$this->_error();
			exit;
		}
		$resultsPerPage = 20;
		if ( c::getPagePiece( 4 ) ) {
			$page = c::getPagePiece( 4 );
		} else {
			$page = 1;
		}

		$docs = Cockpit_Driver_Document_Status::lastUpdatedDocs();

		$start = ( ( $page - 1 ) * $resultsPerPage ) + 1;
		$end = $start + $resultsPerPage;
		$count = 1;

		$list = [];
		foreach( $docs as $doc ){
			if( $count >= $start && $count < $end ){
				$admin = $doc->admin_approved();
				if( $admin ){
					$admin = $admin->name;
				}
				$_doc = [	'id_driver_document_status' => $doc->id_driver_document_status,
									'date' => $doc->date()->format( 'M jS Y' ),
									'time' => $doc->date()->format( 'g:i:s A' ),
									'url' => $doc->url(),
									'doc' => $doc->doc()->name,
									'completed' => ( intval( $doc->completed ) ? true : false ),
									'approved' => $admin
								];
				$_doc = array_merge( $_doc, $doc->driver()->exports( [ 'phone', 'txt', 'email', 'timezone', 'testphone', 'permissions', 'groups', 'vehicle', 'active' ] ) );
				$list[] = $_doc;
			}
			$count++;
		}

		$pages = ceil( $docs->count() / $resultsPerPage );

		$data = [];
		$data[ 'count' ] = $docs->count();
		$data[ 'pages' ] = $pages;
		$data[ 'prev' ] = ( $page > 1 ) ? $page - 1 : null;
		$data[ 'page' ] = intval( $page );
		$data[ 'next' ] = ( $page < $pages ) ? $page + 1 : null;
		$data[ 'results' ] = $list;

		echo json_encode( $data );
	}

	private function _error( $error = 'invalid request', $filename = '' ){
		if( strrpos( $error, 'download' ) === false ){
			echo json_encode( [ 'error' => $error ] );
			exit();
		} else {
			$error = str_replace( 'download:', '', $error );
			header( 'Content-Description: File Transfer' );
			header( 'Content-Type: application/octet-stream' );
			header( 'Content-Disposition: attachment; filename=' . $error . '.txt' );
			header( 'Expires: 0' );
			header( 'Cache-Control: must-revalidate' );
			header( 'Pragma: public' );
			switch ( $error ) {
				case 'file-not-found':
					echo 'File Not Found!';
					break;
				case 'permission-denied':
					echo 'Permission Denied!';
					break;
			}
		}

	}
}