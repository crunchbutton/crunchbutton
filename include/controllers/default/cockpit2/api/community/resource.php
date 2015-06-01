<?php

class Controller_api_community_resource extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if( c::getPagePiece( 3 ) == 'upload' ){
			$this->_upload();
		}

		switch ( $this->method() ) {
			case 'get':
				$this->_get();
			break;
			case 'post':
				$this->_permission();
				$this->_post();
			break;
		}
	}

	private function _permission(){
		if (!c::admin()->permission()->check(['global', 'resource-all' ])) {
			$this->error(401);
		}
	}

	private function _get(){

		switch ( c::getPagePiece( 3 ) ) {

			case 'list':
				$this->_permission();
				$this->_list();
				break;

			case 'driver':
				$this->_driver();
				break;

			case 'download':
				$this->_download();
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

	private function _driver(){
		$resources = [];

		$_resources = Crunchbutton_Community_Resource::byCommunity( 'all' );
		if( $_resources ){
			foreach( $_resources as $resource ){
				if( !$resources[ $community->id_community ] ){
					$resources[ 0 ] = [ 'id_community' => 0, 'name' => 'All Communities', 'resources' => [] ];
				}
				$resources[ 0 ][ 'resources' ][] = $resource->exports();
			}
		}

		$driver = c::user();
		$groups = $driver->groups();
		foreach ( $groups as $group ) {
			if( $group->type == Crunchbutton_Group::TYPE_DRIVER ){
				$_resources = Crunchbutton_Community_Resource::byCommunity( $group->id_community );
				if( $_resources ){
					foreach( $_resources as $resource ){
						if( !$resources[ $community->id_community ] ){
							$resources[ $community->id_community ] = [ 'id_community' => $community->id_community, 'name' => $community->name, 'resources' => [] ];
						}
						$data = $resource->exports();
						$data[ 'communities' ] = null;
						unset( $data[ 'communities' ] );
						$resources[ $community->id_community ][ 'resources' ][] = $data;
					}
				}
			}
		}
		$out = [];
		foreach( $resources as $key => $val ){
			$out[] = $val;
		}
		echo json_encode( $out );exit;
	}

	private function _download(){
		$resource = Crunchbutton_Community_Resource::o( c::getPagePiece( 4 ) );
		if( $resource->id_community_resource ){
			$file = $resource->doc_path();
			$name = $resource->name;
			$ext = pathinfo( $file, PATHINFO_EXTENSION );
			$name .= '.' . $ext;
			if( file_exists( $file ) ){
				header( 'Content-Description: File Transfer' );
				header( 'Content-Type: application/octet-stream' );
				header( 'Content-Disposition: attachment; filename=' . $name );
				header( 'Expires: 0' );
				header( 'Cache-Control: must-revalidate' );
				header( 'Pragma: public' );
				header( 'Content-Length: ' . filesize( $file ) );
				readfile( $file );
				exit;
			} else {
				$this->_error( 'download:file-not-found' );
			}
		}
		$this->_error();
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
		$resource->order_page = ( $this->request()[ 'order_page' ] ? 1 : 0 );
		$resource->side = ( $this->request()[ 'side' ] ? 1 : 0 );
		$resource->active = ( $this->request()[ 'active' ] ? 1 : 0 );
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

	private function _list(){

		$limit = $this->request()['limit'] ? $this->request()['limit'] : 20;
		$search = $this->request()['search'] ? $this->request()['search'] : '';
		$page = $this->request()['page'] ? $this->request()['page'] : 1;
		$community = $this->request()['community'] ? $this->request()['community'] : null;
		if( $community == 'all' ){
			$community = null;
		}
		$keys = [];

		if ($page == 1) {
			$offset = '0';
		} else {
			$offset = ($page-1) * $limit;
		}

		$q = '
			SELECT
				-WILD-
			FROM community_resource cr
		';
		if ($community) {
			$q .= '
				LEFT JOIN community_resource_community crc ON cr.id_community_resource=crc.id_community_resource
			';
		}
		$q .='
			WHERE
				cr.name IS NOT NULL
		';


		if ($community) {
			$q .= '
				AND crc.id_community=?
			';
			$keys[] = $community;
		}

		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'cr.name' => 'like',
					'cr.file' => 'like'
				]
			]);
			$q .= $s['query'];
			$keys = array_merge($keys, $s['keys']);
		}

		// get the count
		$count = 0;
		$r = c::db()->query(str_replace('-WILD-','COUNT(*) c', $q), $keys);
		while ($c = $r->fetch()) {
			$count++;
		}

		$q .= '
			ORDER BY cr.name ASC
			LIMIT ?
			OFFSET ?
		';
		$keys[] = $limit;
		$keys[] = $offset;

		// do the query
		$data = [];
		$r = c::db()->query(str_replace('-WILD-','
			cr.*
		', $q), $keys);

		while ($s = $r->fetch()) {
			$resource = Crunchbutton_Community_Resource::o($s);
			$out = $resource->exports();
			$out['communities'] = [];
			foreach ($resource->communities( true ) as $community) {
				$out['communities'][] = [ 'id_community' => $community->id_community, 'name' => $community->name ];
			}
			$out['page'] = ( intval( $out[ 'page'] ) ) ? true : false;
			$out['order_page'] = ( intval( $out[ 'order_page'] ) ) ? true : false;
			$out['side'] = ( intval( $out[ 'side'] ) ) ? true : false;
			$out['all'] = ( intval( $out[ 'all'] ) ) ? true : false;
			$out['active'] = ( intval( $out[ 'active'] ) ) ? true : false;
			$data[] = $out;
		}

		echo json_encode([
			'count' => intval($count),
			'pages' => ceil($count / $limit),
			'page' => $page,
			'results' => $data
		]);

	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}

}