<?php

class Crunchbutton_Admin extends Cana_Table {
	public static function login($login) {
		return Crunchbutton_Admin::q('select * from admin where login="'.c::db()->escape($login).'" limit 1')->get(0);
	}
	
	public function timezone() {
		if (!isset($this->_timezone)) {
			$this->_timezone = new DateTimeZone($this->timezone);
		}
		return $this->_timezone;
	}
	
	public function permission() {
		if (!isset($this->_permission)) {
			$this->_permission = new Crunchbutton_Acl_Admin($this);
		}
		return $this->_permission;
	}
	
	public function restaurants() {
		if (!isset($this->_restaurants)) {

			if (c::admin()->permission()->check(['global','restaurants-all'])) {
				$restaurants = Restaurant::q('select * from restaurant order by name');

			} else {
				$restaurants = [];
				foreach ($this->permission()->_userPermission as $key => $perm) {
					$find = '/^RESTAURANT-([0-9]+)$/i';
					if (preg_match($find,$key)) {

						$key = preg_replace($find,'\\1',$key);
						$restaurants[$key] = Restaurant::o($key);
					}
				}

			}

			$this->_restaurants = $restaurants;
		}
		return $this->_restaurants;
	}

	public function communities() {
		if (!isset($this->_communities)) {
			$communities = [];

			$q = '
				SELECT COUNT(*) restaurants, community
				FROM restaurant
				WHERE community IS NOT NULL
				AND community != ""
			';

			if (!c::admin()->permission()->check(['global','restaurants-all']) && count($this->restaurants())) {

				foreach ($this->restaurants() as $restaurant) {
					$qa .= ($qa ? ' OR ' : '').' id_restaurant='.$restaurant->id_restaurant.' ';
				}
				$q.= ' AND ( '.$qa.' ) ';

			} elseif (!c::admin()->permission()->check(['global','restaurants-all'])) {
				$q = null;
			}
			
			if ($q) {
				$q .= ' GROUP BY community';
				$communities = c::db()->get($q);
			}
			
			$this->_communities = $communities;

		}

		return $this->_communities;
	}

	public function loginExists( $login ){
		if( trim( $login ) != '' ){
			return Crunchbutton_Admin::login( $login );
		}
		return false;
	}

	public function groups(){
		if( !$this->_groups ){
			$this->_groups = Crunchbutton_Group::q( "SELECT g.* FROM `group` g INNER JOIN admin_group ag ON ag.id_group = g.id_group AND ag.id_admin = {$this->id_admin} ORDER BY name ASC" );
		}
		return $this->_groups;
	}

	public function removeGroups(){
		Cana::db()->query( "DELETE FROM `admin_group` WHERE id_admin = {$this->id_admin}" );
	}

	public function permissions(){
		if( !$this->_permissions ){
			$this->_permissions = c::db()->get( "SELECT * FROM admin_permission WHERE id_admin = {$this->id_admin}" );	
		}
		return $this->_permissions;
	}

	public function hasPermission( $permission ){
		$permissions = $this->permissions();
		foreach( $permissions as $_permission ){
			if( $_permission->permission == $permission && $_permission->allow == 1 ){
				return true;
			}
		}
		return false;
	}

	public function removePermissions(){
		c::db()->query( "DELETE FROM admin_permission WHERE id_admin = {$this->id_admin}" );
	}

	public function addPermissions( $permissions ){
		foreach( $permissions as $key => $val ){
			if( !$this->hasPermission( $key ) ){
				$_permission = new Crunchbutton_Admin_Permission();
				$_permission->id_admin = $this->id_admin;
				$_permission->permission = trim( $key );
				$_permission->allow = 1;
				$_permission->save();
				$dependencies = $_permission->getDependency( $key );
				if( $dependencies ){
					foreach( $dependencies as $dependency ){
						$this->addPermissions( array( $dependency => 1 ) );
					}
				}

			}
		}
	}


	public function hasGroup( $id_group ){
		$groups = $this->groups();
		foreach( $groups as $group ){
			if( $id_group == $group->id_group ){
				return true;
			}
		}
		return false;
	}

	public function groups2str(){
		$groups = $this->groups();
		$str = '';
		$commas = '';
		foreach( $groups as $group ){
			$str .= $commas . $group->name;
			$commas = ', ';
		}
		return $str;
	}
	
	public function makePass($pass) {
		return sha1(c::crypt()->encrypt($pass));
	}

	public static function find($search = []) {

		$query = 'SELECT `admin`.* FROM `admin` WHERE id_admin IS NOT NULL ';
		
		if ( $search[ 'name' ] ) {
			$query .= " AND name LIKE '%{$search[ 'name' ]}%' ";
		}

		$query .= " ORDER BY name DESC";

		$admins = self::q($query);
		return $admins;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('admin')
			->idVar('id_admin')
			->load($id);
	}
}