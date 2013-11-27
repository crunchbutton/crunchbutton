<?php

class Crunchbutton_Group extends Cana_Table {

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('group')
			->idVar('id_group')
			->load($id);
	}

	public function permissions(){
		if( !$this->_permissions ){
			$this->_permissions = c::db()->get( "SELECT * FROM admin_permission WHERE id_group = {$this->id_group}" );	
		}
		return $this->_permissions;
	}

	public function hasPermission( $permission, $useRegex = false ){
		$permissions = $this->permissions();
		foreach( $permissions as $_permission ){
			if( $_permission->permission == $permission && $_permission->allow == 1 ){
				return true;
			}
			if( $useRegex ){
				if( preg_match( $permission, $_permission->permission )  && $_permission->allow == 1 ){
					return true;
				}
			}
		}
		return false;
	}


	public static function find($search = []) {

		$query = 'SELECT `group`.* FROM `group` WHERE id_group IS NOT NULL ';
		
		if ( $search[ 'name' ] ) {
			$query .= " AND name LIKE '%{$search[ 'name' ]}%' ";
		}

		$query .= " ORDER BY name DESC";

		$groups = self::q($query);
		return $groups;
	}

	public function users(){
		if( $this->id_group ){
			return Crunchbutton_Admin_Group::q( "SELECT a.* FROM admin a INNER JOIN admin_group ag ON ag.id_admin = a.id_admin AND ag.id_group = {$this->id_group}" );	
		} 
		return false;
	}

	public function removePermissions(){
		c::db()->query( "DELETE FROM admin_permission WHERE id_group = {$this->id_group}" );
	}

	public function byName( $name ){
		return Crunchbutton_Group::q( "SELECT * FROM `group` WHERE name='{$name}'" );
	}

	public function addPermissions( $permissions ){
		if( $permissions && is_array( $permissions ) ){
			foreach( $permissions as $key => $val ){
				if( !$this->hasPermission( $key ) ){
					$_permission = new Crunchbutton_Admin_Permission();
					$_permission->id_group = $this->id_group;
					$_permission->permission = trim( $key );
					$_permission->allow = 1;
					$_permission->save();
					// reset the permissions
					$this->_permissions = false;
					$dependencies = $_permission->getDependency( $key );
					if( $dependencies ){
						foreach( $dependencies as $dependency ){
							$this->addPermissions( array( $dependency => 1 ) );
						}
					}
				}
			}
		}
	}

	public function usersTotal(){
		if( $this->id_group ){
			return Crunchbutton_Admin_Group::q( "SELECT a.* FROM admin a INNER JOIN admin_group ag ON ag.id_admin = a.id_admin AND ag.id_group = {$this->id_group}" )->count();	
		} 
		return 0;
	}

}