<?php

class Controller_api_staff_list extends Crunchbutton_Controller_RestAccount {

	public function init() {

		$hasPermission = ( c::admin()->permission()->check( ['global', 'permission-all', 'permission-users'] ) );
		if( !$hasPermission ){
			$this->_error();
			exit;
		}

		$resultsPerPage = 20;

		if ( $this->request()[ 'page' ] ) {
			$page = $this->request()[ 'page' ];
		} else {
			$page = 1;
		}

		$search = [ 'name' => $this->request()[ 'name' ],
								'type' => $this->request()[ 'type' ],
								'status' => $this->request()[ 'status' ] ];


		$staff = Crunchbutton_Admin::search( $search );

		$start = ( ( $page - 1 ) * $resultsPerPage ) + 1;
		$end = $start + $resultsPerPage;
		$count = 1;

		$list = [];
		foreach( $staff as $worker ){
			if( $count >= $start && $count < $end ){
				$data = $worker->exports( [ 'permissions', 'groups' ] );
				unset( $data[ 'email' ] );
				unset( $data[ 'timezone' ] );
				unset( $data[ 'testphone' ] );
				unset( $data[ 'txt' ] );
				$list[] = $data;
			}
			$count++;
		}

		$start = ( ( $page - 1 ) * $resultsPerPage ) + 1;
		$end = $start + $resultsPerPage;
		$count = 1;

		$pages = ceil( $staff->count() / $resultsPerPage );

		$data = [];
		$data[ 'count' ] = $staff->count();
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