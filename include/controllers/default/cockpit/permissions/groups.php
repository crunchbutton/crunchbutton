<?php

class Controller_Permissions_Groups extends Crunchbutton_Controller_Account {
	
	public function init() {
		
		if (!c::admin()->permission()->check(['global','permissions-all', 'permission-groups'])) {
			return ;
		}

		c::view()->page = 'permissions';

		$action = c::getPagePiece(2);

		switch ( $action ) {

			case 'permissions':
				$this->form_permissions();
				break;

			case 'content':
				$this->search();
				break;

			case 'new':
				$this->form();
				break;

			case 'remove':
				$id_group = $_REQUEST[ 'id_group' ];
				$group = Crunchbutton_Group::o( $id_group );
				if( $group->id_group ){
					$group->delete();
				}
				echo 'ok';
				break;

			default:
				if( is_numeric( $action ) ){
					$this->form();
					exit;
				}
				c::view()->display('permissions/groups/index');
				break;
		}
	
	}

	private function search(){
		$search = [];
		if ( $_REQUEST[ 'name' ] ) {
			$search[ 'name' ] = $_REQUEST[ 'name' ];
		}
		c::view()->groups = Crunchbutton_Group::find( $search );
		c::view()->layout( 'layout/ajax' );
		c::view()->display( 'permissions/groups/content' );
	}

	private function form_permissions(){
		if( c::getPagePiece(3) ){
			c::view()->group = Crunchbutton_Group::o( c::getPagePiece(3) );
			$permissions = new Crunchbutton_Admin_Permission();
			c::view()->permissions = $permissions->all();
			c::view()->elements = $permissions->elements();
			c::view()->display( 'permissions/groups/permissions' );	
		}
	}

	private function form(){
		$id_group = c::getPagePiece(2);
		if( $id_group != 'new' ){
			c::view()->group = Crunchbutton_Group::o( $id_group );
		} else {
			c::view()->group = new Crunchbutton_Group();
		}
		c::view()->display( 'permissions/groups/form' );
	}

}