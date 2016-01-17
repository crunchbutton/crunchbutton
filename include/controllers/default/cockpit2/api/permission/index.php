<?php

class Controller_Api_Permission extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (!c::admin()->permission()->check(['global','permissions-all', 'permission-users'])) {
			$this->error(401, true);
		}

		switch ( $this->method() ) {
			case 'get':
				echo $this->_permissions();
				break;

			case 'post':
				echo $this->_save();
				break;
		}
	}

	private function _save(){

		switch ( c::getPagePiece( 2  ) ) {
			case 'staff':
				$staff = Admin::login( $this->request()[ 'id_admin' ], true);
				if( $staff->id_admin ){
					$this->_saveAdmin( $staff );
				} else {
					$this->error(404, true);
				}
				break;

			case 'group':
				$group = Crunchbutton_Group::byName( c::getPagePiece( 3 ) );
				$group = $group->get( 0 );
				if( $group->id_group ){
					$this->_saveGroup( $group );
				} else {
					$this->error(404, true);
				}
				break;
			default:
				$this->error(404, true);
				break;
		}
	}

	private function _saveGroup( $group ){
		$group->removePermissions();
		$permissions = $this->request()[ 'permissions' ];
		if( $permissions && is_array( $permissions ) ){
			foreach( $permissions as $name ){
				if( !$group->hasPermission( $name ) ){
					$_permission = new Crunchbutton_Admin_Permission();
					$_permission->id_group = $group->id_group;
					$_permission->permission = trim( $name );
					$_permission->allow = 1;
					$_permission->save();
					$dependencies = $_permission->getDependency( $name );
					if( $dependencies ){
						foreach( $dependencies as $dependency ){
							$group->addPermissions( array( $dependency => 1 ) );
						}
					}

				}
			}
		}
		$group->_permissions = false;
		echo json_encode( [ 'success' => $group->id_group ] );
	}

	private function _saveAdmin( $staff ){
		$staff->removePermissions();
		$permissions = $this->request()[ 'permissions' ];
		if( $permissions && is_array( $permissions ) ){
			foreach( $permissions as $name ){
				if( !$staff->hasPermission( $name ) ){
					$_permission = new Crunchbutton_Admin_Permission();
					$_permission->id_admin = $staff->id_admin;
					$_permission->permission = trim( $name );
					$_permission->allow = 1;
					$_permission->save();
					$dependencies = $_permission->getDependency( $name );
					if( $dependencies ){
						foreach( $dependencies as $dependency ){
							$staff->addPermissions( array( $dependency => 1 ) );
						}
					}

				}
			}
		}
		$staff->_permissions = false;
		echo json_encode( [ 'success' => $staff->id_admin ] );
	}

	private function _permissions(){

		$out = [ 'permissions' => [] ];

		if( c::getPagePiece( 2 ) == 'staff' ){
			if( c::getPagePiece( 3 ) ){
				$staff = Admin::o( c::getPagePiece( 3 ) );
				if (!$staff->id_admin) {
					$staff = Admin::login( c::getPagePiece( 3 ), true );
				}
				$out[ 'info' ] = [ 'type' => 'Staff', 'name' => $staff->name ];
			} else {
				$this->error( 404 );
			}
		} else if( c::getPagePiece( 2 ) == 'group' ){

			if( c::getPagePiece( 3 ) ){
				$group = Crunchbutton_Group::o( c::getPagePiece( 3 ) );
				if( !$group->id_group ){
					$group = Crunchbutton_Group::byName( c::getPagePiece( 3 ) );
					$group = $group->get( 0 );
				}
				$out[ 'info' ] = [ 'type' => 'Group', 'name' => $group->name ];
			} else {
				$this->error( 404 );
			}
		}


		$permissions = new Crunchbutton_Admin_Permission();
		$elements = $permissions->elements();
		$groups = $permissions->all();

		if( $staff->id_admin ){
			$element = $staff;
		} else if( $group->id_group ){
			$element = $group;
		} else {
			$element = null;
		}

		foreach( $groups as $name => $_group ){

			$_permissions = $_group[ 'permissions' ];
			$_group[ 'group' ] = $name;
			$_group[ 'permissions' ] = [];

			foreach( $_permissions as $name => $permission ){

				$permission[ 'name' ] = $name;

				$permission[ 'group' ] = $_group[ 'group' ];

				switch ( $permission[ 'type' ] ) {

					case 'combo':

						$permission[ 'permitted' ] = [];
						if( $element ){
							$options = $elements[ $permission[ 'element' ] ];
							foreach( $options as $option ){
								$value = str_replace( 'ID' , $option->id, $name);
								if ( $element->hasPermission( $value ) ){
									$permission[ 'permitted' ][] = intval( $option->id );
								}
							}
						}

						break;

					default:

						if( $element ){
							$permission[ 'has' ] = ( $element->hasPermission( $name ) ? true : false );
						} else {
							$permission[ 'has' ] = false;
						}

						break;
				}

				if( $permission[ 'additional' ] ){
					$_additional_permissions = $permission[ 'additional' ][ 'permissions' ];
					$permission[ 'additional' ][ 'permissions' ] = [];
					foreach ( $_additional_permissions as $additional_name => $additional_permission ) {
						$additional_permission[ 'name' ] = $additional_name;
						if( $element ){
							$additional_permission[ 'has' ] = ( $element->hasPermission( $additional_name ) ? true : false );
						} else {
							$additional_permission[ 'has' ] = true;
						}
						$additional_permission[ 'group' ] = $_group[ 'group' ];
						$permission[ 'additional' ][ 'permissions' ][] = $additional_permission;
					}
				}
				$_group[ 'permissions' ][] = $permission;
			}

			$out[ 'permissions' ][] = $_group;
		}
		echo json_encode( $out );exit;
	}
}