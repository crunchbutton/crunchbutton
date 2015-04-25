<?php

class Controller_api_community_resource extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (!c::admin()->permission()->check(['global', 'resource-all' ])) {
			header('HTTP/1.1 401 Unauthorized');
			exit;
		}

		if( c::getPagePiece( 3 ) == 'upload' ){
			$this->_upload();
		}


		switch ( $this->method() ) {
			case 'get':
				$this->_get();
			break;
			case 'post':
				$this->_post();
			break;
		}
	}

	private function _get(){
		switch ( $resource ) {
			case 'by-community':
				# code...
				break;

			default:
				$resource = Crunchbutton_Community_Resource::o( c::getPagePiece( 3 ) );
				if( $resource->id_community_resource ){
					echo $resource->json(); exit();
				}
				$this->_error();
				break;
		}

	}

	private function _upload(){

		if( $_FILES ){
			$ext = pathinfo( $_FILES['file']['name'], PATHINFO_EXTENSION );
			if( Util::allowedExtensionUpload( $ext ) ){
				$name = pathinfo( $_FILES['file']['name'], PATHINFO_FILENAME );
				$name = str_replace( $ext, '', $name );
				$random = substr( str_replace( '.' , '', uniqid( rand(), true ) ), 0, 8 );
				$name = Util::slugify( $random . '-' . $name );
				$name = substr( $name, 0, 40 ) . '.'. $ext;
				$file = Crunchbutton_Community_Resource::path() . $name;

				if( !file_exists( Util::uploadPath() ) ){
					$this->_error( '"www/upload" folder doesn`t exist!' );
				}

				if( !file_exists( Crunchbutton_Community_Resource::path() ) ){
					$this->_error( '"www/upload/resource/" folder doens`t exist!' );
				}

				if ( copy( $_FILES[ 'file' ][ 'tmp_name' ], $file ) ) {
					chmod( $file, 0777 );
				}
				echo json_encode( [ 'success' => $name ] );
				exit;
			} else {
				$this->_error( 'invalid extension' );
			}
		} else {
			$this->_error();
		}
	}

	private function _post(){
		$this->_save();
	}

	private function _save(){

		if( $this->request()[ 'id_community_resource' ] ){
			$resource = Crunchbutton_Community_Resource::o( $this->request()[ 'id_community_resource' ] );
		}
		if( !$resource->id_community_resource ){
			$resource = new Crunchbutton_Community_Resource;
		}

		$resource->id_admin = c::user()->id_admin;
		$resource->name = $this->request()[ 'name' ];
		$resource->file = $this->request()[ 'file' ];
		$resource->all = ( $this->request()[ 'all' ] ? 1 : 0 );
		$resource->page = ( $this->request()[ 'page' ] ? 1 : 0 );
		$resource->side = ( $this->request()[ 'side' ] ? 1 : 0 );
		$resource->date = date( 'Y-m-d H:i:s' );
		$resource->save();

		Crunchbutton_Community_Resource_Community::removeByResource( $resource->id_community_resource );

		if( !$resource->all ){
			$communities = $this->request()[ 'communities' ];
			foreach( $communities as $id_community ){
				$community = new Crunchbutton_Community_Resource_Community();
				$community->id_community_resource = $resource->id_community_resource;
				$community->id_community = $id_community;
				$community->save();
			}
		}

		echo json_encode( [ 'id_resource' => $resource->id_community_resource ] );
		exit;
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}

}