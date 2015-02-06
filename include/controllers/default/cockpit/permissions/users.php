<?php

class Controller_Permissions_Users extends Crunchbutton_Controller_Account {

	public function init() {


		if (!c::admin()->permission()->check(['global','permissions-all', 'permission-users'])) {
			return ;
		}

		c::view()->page = 'permissions';

		$action = c::getPagePiece(2);

		switch ( $action ) {

			case 'permissions':
				$this->form_permissions();
				break;

			case 'notifications':
				$this->form_notifications();
				break;

			case 'content':
				$this->search();
				break;

			case 'new':
				$this->form();
				break;

			case 'remove':
				$id_admin = $_REQUEST[ 'id_admin' ];
				$admin = Crunchbutton_Admin::o( $id_admin );
				if( $admin->id_admin ){
					$admin->delete();
				}
				echo 'ok';
				break;

			default:
				if( is_numeric( $action ) ){
					$this->form();
					exit;
				}
				c::view()->display('permissions/users/index');
				break;
		}
	}

	private function search(){
		$search = [];
		if ( $_REQUEST[ 'name' ] ) {
			$search[ 'name' ] = $_REQUEST[ 'name' ];
		}
		c::view()->admins = Crunchbutton_Admin::find( $search );
		c::view()->layout( 'layout/ajax' );
		c::view()->display( 'permissions/users/content' );
	}

	private function form_notifications(){
	if( c::getPagePiece(3) ){
			c::view()->admin = Crunchbutton_Admin::o( c::getPagePiece(3) );
			c::view()->display( 'permissions/users/notifications' );
		}
	}

	private function form_permissions(){
		if( c::getPagePiece(3) ){
			c::view()->admin = Crunchbutton_Admin::o( c::getPagePiece(3) );
			$permissions = new Crunchbutton_Admin_Permission();
			c::view()->permissions = $permissions->all();
			c::view()->elements = $permissions->elements();
			c::view()->display( 'permissions/users/permissions' );
		}
	}

	private function form(){
		$id_admin = c::getPagePiece(2);
		if( $id_admin != 'new' ){
			c::view()->admin = Crunchbutton_Admin::o( $id_admin );
		} else {
			c::view()->admin = new Crunchbutton_Admin();
		}
		c::view()->display( 'permissions/users/form' );
	}

}