<?php

class Controller_Api_Staff_Permission extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (!c::admin()->permission()->check(['global','permissions-all', 'permission-users'])) {
			$this->error(401);
		}

		switch ( $this->method() ) {
			case 'get':
				echo $this->_permissions();
				break;

			case 'post':
				// do nothing for now
				break;
		}
	}

	private function _permissions(){

		if( c::getPagePiece( 3 ) ){

			$staff = Admin::o(c::getPagePiece(3));

			if (!$staff->id_admin) {
				$staff = Admin::login(c::getPagePiece(3), true);
			}
		}

		$out = [ 'permissions' => [] ];
		$permissions = new Crunchbutton_Admin_Permission();
		$permissions = $permissions->all();
		foreach( $permissions as $key => $val ){

			$val[ 'name' ] = $key;
			if( $staff->id_admin ){
				$val[ 'has' ] = ( $staff->hasPermission( $key ) ? '1' : '0' );
			} else {
				$val[ 'has' ] = '0';
			}

			$permissions = $val[ 'permissions' ];
			$val[ 'permissions' ] = [];
			foreach( $permissions as $key1 => $val1 ){
				$val1[ 'name' ] = $key1;
				$val1[ 'name' ] = $key;
				if( $staff->id_admin ){
					$val1[ 'has' ] = ( $staff->hasPermission( $key ) ? '1' : '0' );
				} else {
					$val1[ 'has' ] = '0';
				}
				$val[ 'permissions' ][] = $val1;
			}

			$out[ 'permissions' ][] = $val;
		}
		echo json_encode( $out );exit;
	}
}