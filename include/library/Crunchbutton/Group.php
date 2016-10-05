<?php

class Crunchbutton_Group extends Cana_Table {

	const DRIVER_GROUPS_PREFIX = 'drivers-';
	const MARKETING_REP_GROUPS_PREFIX = 'mktrep-';
	const MARKETING_REP_GROUP = 'marketing-rep';
	const CAMPUS_MANAGER_GROUP = 'campus-manager';
	const COMMUNITY_CS_GROUP = 'community-cs';

	const TYPE_MARKETING_REP = 'marketing-rep';
	const TYPE_COMMUNITY_DIRECTOR = 'comm-director';
	const TYPE_DRIVER = 'driver';
	const TYPE_COMMUNITY_MANAGER = 'community-manager';
	const TYPE_BRAND_REPRESENTATIVE = 'brand-representative';
	const TYPE_SUPPORT = 'support';
	const TYPE_COMMUNITY_CS = 'community-cs';


	public function driverGroupOfCommunity( $community ){
		return Crunchbutton_Group::normalizeDriverGroup( str_replace( ' ' , '-', Crunchbutton_Group::DRIVER_GROUPS_PREFIX . strtolower( str_replace( "'", '', str_replace( '"', '', str_replace( ".", '', $community ) ) ) ) ), 0, 20);
	}

	public function marketingRepGroupOfCommunity( $community ){
		return Crunchbutton_Group::normalizeDriverGroup( str_replace( ' ' , '-', Crunchbutton_Group::MARKETING_REP_GROUPS_PREFIX . strtolower( str_replace( "'", '', str_replace( '"', '', str_replace( ".", '', $community ) ) ) ) ), 0, 20);
	}

	public function communityDirectorGroupCommunity($id_community){
		return self::createCommunityDirectorGroup($id_community);
	}

	// used at admin_group #7387
	public function getType(){
		if( $this->name == self::TYPE_SUPPORT ){
			return self::TYPE_SUPPORT;
		}
		if( $this->name == self::COMMUNITY_CS_GROUP ){
			return self::TYPE_COMMUNITY_CS;
		}
		if( $this->name == self::CAMPUS_MANAGER_GROUP ){
			return self::TYPE_COMMUNITY_MANAGER;
		}
		if( $this->type == self::TYPE_DRIVER ){
			return self::TYPE_DRIVER;
		}
		if( $this->type == self::TYPE_MARKETING_REP ){
			return self::TYPE_BRAND_REPRESENTATIVE;
		}
		if( $this->type == self::TYPE_COMMUNITY_DIRECTOR ){
			return self::TYPE_COMMUNITY_DIRECTOR;
		}
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('group')
			->idVar('id_group')
			->load($id);
	}

	public function getRestaurantCommunityName( $community ){
		die('#5430 deprecated');

		$communities = Restaurant::getCommunities();
		foreach( $communities as $_community ){
			if( Crunchbutton_Group::driverGroupOfCommunity( $_community ) == $community ){
				return $_community;
			}
		}
	}

	public function normalizeDriverGroup( $community ){
		return substr( $community, 0, 120 );
	}

	public function getDeliveryGroupByCommunity( $community ){

		if( !$community ){
			die( 'Error:getDeliveryGroupByCommunity' );
		}

		$community = Crunchbutton_Group::normalizeDriverGroup( $community );

		$group = Crunchbutton_Group::byName( $community );
		if( $group->id_group ){
			return $group;
		}

		// Get the community name
		$description = Crunchbutton_Group::getRestaurantCommunityName( $community );

		$description .= ' drivers group';
		$group = new Crunchbutton_Group();
		$group->name = $community;
		$group->description = $description;
		$group->save();
		return $group;
	}

	public function createDriverGroup( $name, $description, $id_community ){
		$description = $description . ' drivers group';
		$group = new Crunchbutton_Group();
		$group->name = $name;
		$group->type = Crunchbutton_Group::TYPE_DRIVER;
		$group->id_community = $id_community;
		$group->description = $description;
		$group->save();
		return $group;
	}

	public function createCommunityDirectorGroup( $id_community ){
		$community = Crunchbutton_Community::o( $id_community );
		if( $community->id_community ){
			$name = self::TYPE_COMMUNITY_DIRECTOR .'-'. $id_community;
			$group = self::q('SELECT * FROM `group` WHERE name = ? ORDER BY id_group DESC LIMIT 1',[$name]);
			if(!$group->id_group){
				$description = $community->name . ' community director group';
				$group = new Crunchbutton_Group();
				$group->name = $name;
				$group->description = $description;
				$group->type = self::TYPE_COMMUNITY_DIRECTOR;
				$group->id_community = $id_community;
				$group->save();
			}
			return $group;
		}
		return null;
	}

	public function createMarketingRepGroup( $id_community ){
		$community = Crunchbutton_Community::o( $id_community );
		if( $community->id_community ){
			$name = $community->marketingRepGroup();
			$description = $community->name . ' brand rep group';

			$group = new Crunchbutton_Group();
			$group->name = $name;
			$group->description = $description;
			$group->type = Crunchbutton_Group::TYPE_MARKETING_REP;
			$group->id_community = $id_community;
			$group->save();
		}
		return $group;
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
			if( $_permission->permission == $permission && $_permission->allow){
				return true;
			}
			if( $useRegex ){
				if( preg_match( $permission, $_permission->permission )  && $_permission->allow ){
					return true;
				}
			}
		}
		return false;
	}


	public function exports(){
		$out = $this->properties();
		if( $this->id_community ){
			$out[ 'community' ] = $this->community()->name;
		}
		$out[ 'members' ] = $this->users()->count();
		return $out;
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
			return Crunchbutton_Admin::q( "SELECT a.* FROM admin a INNER JOIN admin_group ag ON ag.id_admin = a.id_admin AND ag.id_group = {$this->id_group}" );
		}
		return false;
	}

	public function removeUsers(){
		Crunchbutton_Admin_Group::q( 'SELECT * FROM `admin_group` WHERE id_group = ?', [$this->id_group] )->delete();
	}

	public function removePermissions(){
		c::dbWrite()->query( "DELETE FROM admin_permission WHERE id_group = {$this->id_group}" );
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

	public function community(){
		if( !$this->_community && $this->id_community ){
			$this->_community = Crunchbutton_Community::o( $this->id_community );
		}
		return $this->_community;
	}

	public function hasUser( $id_admin ){
		$admin_group = Crunchbutton_Admin_Group::q( "SELECT * FROM admin_group ag WHERE ag.id_group = {$this->id_group} AND ag.id_admin = {$id_admin} LIMIT 1" );
		if( $admin_group->id_admin_group ){
			return true;
		}
		return false;
	}

	public function usersTotal(){
		if( $this->id_group ){
			return Crunchbutton_Admin_Group::q( "SELECT a.* FROM admin a INNER JOIN admin_group ag ON ag.id_admin = a.id_admin AND ag.id_group = {$this->id_group}" )->count();
		}
		return 0;
	}

}
