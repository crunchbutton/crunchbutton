<?php

class Crunchbutton_Admin extends Cana_Table {

	const CONFIG_RECEIVE_DRIVER_SCHEDULE_SMS_WARNING = 'schedule-sms';


	public static function login($login, $inactive = false) {
		$status = ( $inactive ? '' : 'and active = 1' );
		return Crunchbutton_Admin::q('select * from admin where login="'.c::db()->escape($login).'"' . $status . ' limit 1')->get(0);
	}

	public function publicExports() {
		return [
			'name' => $this->name,
			'id_admin' => $this->id_admin
		];
	}

	public function payment_type(){
		return Crunchbutton_Admin_Payment_Type::byAdmin( $this->id_admin );
	}

	public function validateLogin( $login, $increment = 0 ){
		$test = $login . ( $increment > 0 ? $increment : '' );
		$admin = Crunchbutton_Admin::login( $test, true );
		if( $admin->id_admin ){
			$increment++;
			return Crunchbutton_Admin::validateLogin( $login, $increment );
		}
		return $test;
	}

	public function firstName(){
		$name = explode( ' ', $this->name );
		if( trim( $name[ 0 ] ) != '' ){
			return $name[ 0 ];
		}
		return $this->name;
	}

	public function createLogin(){
		if( $this->login ){
			return $this->login;
		}
		$login = str_replace( '-' , '', Util::slugify( $this->name ) );
		$login = Crunchbutton_Admin::validateLogin( $login );
		return $login;
	}

	public function getByPhone( $phone ){
		return Crunchbutton_Admin::q( "SELECT * FROM admin a WHERE REPLACE( REPLACE( a.txt, ' ', '' ), '-', '' ) = '{$phone}' OR REPLACE( REPLACE( a.phone, ' ', '' ), '-', '' ) = '{$phone}' ORDER BY id_admin DESC LIMIT 1 " );
	}

	public function getByPhoneSetup( $phone ){
		return Crunchbutton_Admin::q( "SELECT * FROM admin a WHERE a.phone = '{$phone}' AND ( a.pass IS NULL OR a.pass = '' ) ORDER BY id_admin DESC LIMIT 1 " );
	}

	public function getCSAdminByPhone( $phone ){
		$group = Crunchbutton_Group::byName( Config::getVal( Crunchbutton_Support::CUSTOM_SERVICE_GROUP_NAME_KEY ) );
		return Crunchbutton_Admin::q( "SELECT * FROM admin a INNER JOIN admin_group ag ON ag.id_admin = a.id_admin AND ag.id_group = '" . $group->id_group ."' WHERE REPLACE( REPLACE( a.txt, ' ', '' ), '-', '' ) = '{$phone}' OR REPLACE( REPLACE( a.phone, ' ', '' ), '-', '' ) = '{$phone}' ORDER BY a.id_admin DESC LIMIT 1 " );
	}

	public function checkIfThePhoneBelongsToAnAdmin( $phone ){
		$phone = str_replace( '-', '', $phone );
		$phone = str_replace( ' ', '', $phone );
		return Crunchbutton_Admin::q( "SELECT * FROM admin WHERE REPLACE( phone, '-', '' ) = '$phone' OR REPLACE( txt, '-', '' ) = '$phone' OR REPLACE( testphone, '-', '' ) = '$phone'" );
	}

	public function totalOrdersDelivered(){
		$query = 'SELECT COUNT( DISTINCT( o.id_order ) ) AS Total FROM `order` o
								INNER JOIN order_action oa on oa.id_order = o.id_order
							WHERE oa.id_admin = ' . $this->id_admin . ' AND
							( oa.type = "' . Crunchbutton_Order_Action::DELIVERY_PICKEDUP . '" ||
								oa.type = "' . Crunchbutton_Order_Action::DELIVERY_ACCEPTED . '" ||
								oa.type = "' . Crunchbutton_Order_Action::DELIVERY_DELIVERED . '" )';
		$result = c::db()->get( $query );
		return $result->_items[0]->Total;
	}

	public function timezone() {
		if (!isset($this->_timezone)) {
			$this->_timezone = new DateTimeZone($this->timezone);
		}
		return $this->_timezone;
	}

	public function phone(){
		$phone = $this->phone;
		$phone = preg_replace('/[^\d]*/i','',$phone);
		$phone = preg_replace('/(\d{3})(\d{3})(.*)/', '\\1-\\2-\\3', $phone);
		return $phone;
	}

	public function getTxtNumber(){
		if( $this->txt ){
			return $this->txt;
		}
		$notifications = Crunchbutton_Admin_Notification::q( "SELECT * FROM admin_notification WHERE id_admin = {$this->id_admin} AND active = 1" );
		foreach( $notifications as $notification ){
			if( $notification->type == Crunchbutton_Admin_Notification::TYPE_SMS ){
				return $notification->value;
			}
		}
		return false;
	}

	public function getPhoneNumber(){
		if( $this->phone ){
			return $this->phone;
		}
		$notifications = Crunchbutton_Admin_Notification::q( "SELECT * FROM admin_notification WHERE id_admin = {$this->id_admin} AND active = 1" );
		foreach( $notifications as $notification ){
			if( $notification->type == Crunchbutton_Admin_Notification::TYPE_PHONE ){
				return $notification->value;
			}
		}
		return false;
	}

	public function getAdminsWithNotifications(){
		return Crunchbutton_Admin::q( 'SELECT DISTINCT( a.id_admin ), a.name FROM admin a INNER JOIN admin_notification an ON an.id_admin = a.id_admin ORDER BY a.name ASC' );
	}

	public function activeNotifications(){
		if( !$this->_activeNotifications ){
			if( $this->id_admin ){
				$this->_activeNotifications = Crunchbutton_Admin_Notification::q( "SELECT * FROM admin_notification WHERE id_admin = {$this->id_admin} AND active = 1" );
			}
		}
		return $this->_activeNotifications;
	}

	public function restaurantsHeDeliveryFor(){
		return Restaurant::q( 'SELECT DISTINCT( r.id_restaurant ) id, r.* FROM restaurant r INNER JOIN notification n ON n.id_restaurant = r.id_restaurant AND n.type = "' . Crunchbutton_Notification::TYPE_ADMIN . '" AND n.active = 1 AND r.active = 1 AND n.id_admin = ' . $this->id_admin );
	}

	public function driversList( $search = '' ){

		$where = ( $search && trim( $search ) != '' ) ? ' AND a.name LIKE "%' . $search . '%"' : '';

		return Admin::q( 'SELECT DISTINCT(a.id_admin) id, a.* FROM admin a INNER JOIN driver_log dl ON dl.id_admin = a.id_admin WHERE 1=1 ' . $where . ' ORDER BY a.name ASC' );
	}

	public function search( $search = [] ){

		$where = 'WHERE 1=1 ';
		if( $search[ 'name' ] && trim( $search[ 'name' ] ) ){
			$where .= ' AND a.name LIKE "%' . $search[ 'name' ] . '%"';
		}

		if( $search[ 'status' ] && $search[ 'status' ] != 'all' ){
			$active = ( $search[ 'status' ] == 'active' ) ? '1' : '0';
			$where .= ' AND a.active = "' . $active . '"';
		}

		$query = 'SELECT a.* FROM admin a ' . $where . ' ORDER BY a.name ASC';

		switch ( $search[ 'type' ] ) {
			case 'drivers':
				$query = 'SELECT DISTINCT(a.id_admin) AS id, a.* FROM admin a
											INNER JOIN admin_group ag ON ag.id_admin = a.id_admin
											INNER JOIN `group` g ON g.id_group = ag.id_group AND g.name LIKE "' . Crunchbutton_Group::DRIVER_GROUPS_PREFIX . '%"
											INNER JOIN admin_notification an ON a.id_admin = an.id_admin ' . $where . 'ORDER BY a.name ASC';
				break;
		}
		return Admin::q( $query );
	}

	public function drivers(){
		return Admin::q( 'SELECT a.* FROM admin a
												INNER JOIN (
													SELECT DISTINCT(id_admin) FROM (
													SELECT DISTINCT(a.id_admin) FROM admin a INNER JOIN admin_notification an ON a.id_admin = an.id_admin AND an.active = 1
													UNION
													SELECT DISTINCT(a.id_admin) FROM admin a
														INNER JOIN admin_group ag ON ag.id_admin = a.id_admin
														INNER JOIN `group` g ON g.id_group = ag.id_group AND g.name LIKE "' . Crunchbutton_Group::DRIVER_GROUPS_PREFIX . '%"
														INNER JOIN admin_notification an ON a.id_admin = an.id_admin AND an.active = 1
														) drivers
													)
											drivers ON drivers.id_admin = a.id_admin AND a.active = 1 ORDER BY name ASC' );
	}

	public function allPlacesHeDeliveryFor(){
		$deliveryFor = [];
		$restaurants = $this->restaurantsHeDeliveryFor();
		foreach( $restaurants as $restaurant ){
			$deliveryFor[ $restaurant->id_restaurant ] = $restaurant->id_restaurant;
		}
		$adminCommunities = [];
		$groups = $this->groups();
		foreach ( $groups as $group ) {
			$communities = Crunchbutton_Community::communityByDriverGroup( $group->name );
			foreach( $communities as $community ){
				$restaurants = $community->getRestaurants();
				foreach( $restaurants as $restaurant ){
					if( $restaurant->delivery_service ){
						$deliveryFor[ $restaurant->id_restaurant ] = $restaurant->id_restaurant;
					}
				}
			}
		}

		// legacy
		foreach ( $groups as $group ) {
			if( strpos( $group->name, Crunchbutton_Group::DRIVER_GROUPS_PREFIX ) !== false ){
				$community = str_replace( Crunchbutton_Group::DRIVER_GROUPS_PREFIX, '', $group->name );
				$restaurants = Restaurant::getRestaurantsByCommunity( $community, true );
				foreach( $restaurants as $restaurant ){
					if( $restaurant->delivery_service ){
						$deliveryFor[ $restaurant->id_restaurant ] = $restaurant->id_restaurant;
					}
				}
			}
		}
		return $deliveryFor;
	}

	public function isDriver(){
		$query = 'SELECT COUNT(*) AS Total FROM admin_group ag INNER JOIN `group` g ON g.id_group = ag.id_group WHERE ag.id_admin = ' . $this->id_admin . ' AND g.name LIKE "drivers-%"';
		$result = c::db()->get( $query );
		return ( $result->_items[0]->Total > 0 );
	}

	public function communitiesHeDeliveriesFor(){

		$deliveryFor = [];
		if( $this->id_admin ){
			$communities = Crunchbutton_Community::q( 'SELECT * FROM community ORDER BY name ASC' );
			foreach( $communities as $community ){
				if( $community->driverDeliveryHere( $this->id_admin ) ){
					$deliveryFor[] = $community;
				}
			}
		}
		return $deliveryFor;
	}

	public function isWorking(){
		$now = new DateTime( 'now', $this->timezone() );
		$now = $now->format( 'YmdHi' );
		$hours = Admin_Hour::q( "SELECT * FROM admin_hour WHERE
															id_admin = {$this->id_admin} AND
 															DATE_FORMAT( date_start, '%Y%m%d%H%i' ) <= {$now} AND
  														DATE_FORMAT( date_end, '%Y%m%d%H%i' ) >= {$now} ");
		return ( $hours->count() > 0 );
	}

	public function workingHoursWeek(){

		$date = date( 'Y\WW' );
		$weekdays = [ 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri','Sat' ];
		$hours = [];
		for( $i = 0; $i <= 6; $i++ ){
			$day = $date . $i;
			$day = new DateTime( $day, $this->timezone() );
			$_hours = Crunchbutton_Admin_Hour::segmentsByDate( $day->format( 'Y-m-d' ), $_join = ', ', $this->id_admin );
			if( $_hours[ $this->id_admin ] && $_hours[ $this->id_admin ][ 'hours' ] ){
				if( !$hours[ $_hours[ $this->id_admin ][ 'hours' ] ] ){
					$hours[ $_hours[ $this->id_admin ][ 'hours' ] ] = [];
				}
				$hours[ $_hours[ $this->id_admin ][ 'hours' ] ][] = $weekdays[ $i ];
			}
		}
		$weekdays = [];
		foreach( $hours as $hour => $weekday ){
			$weekdays[ join( $weekday, ', ' ) ] = $hour;
		}
		return $weekdays;
	}

	public function getNotifications( $oderby = 'active DESC, id_admin_notification DESC' ){
		return Crunchbutton_Admin_Notification::q( "SELECT * FROM admin_notification WHERE id_admin = {$this->id_admin} ORDER BY {$oderby}" );
	}

	public function getAllPermissionsName(){
		return c::db()->get( "SELECT DISTINCT( ap.permission ) FROM admin_permission ap WHERE ap.id_admin = {$this->id_admin} OR ap.id_group IN ( SELECT id_group FROM admin_group WHERE id_admin = {$this->id_admin} )" );
	}

	public function permissionCuration() {
		return false;

		/**
		 why is all this permissions stuff so unorganized!

		$_permissions = new Crunchbutton_Admin_Permission();
		$restaurants_ids = Restaurant::restaurantsUserHasPermission();
		$all = $_permissions->all();
		// Get all restaurants permissions
		$permissions = c::admin()->getAllPermissionsName();
		$communities = array();
		foreach ( $permissions as $permission ) {
			$permission = $permission->permission;
			$info = $_permissions->getPermissionInfo( $permission );
			$name = $info[ 'permission' ];
			if( $name == 'curation-communities-ID' ){
				if( strstr( $name, 'ID' ) ){
					$regex = str_replace( 'ID' , '((.)*)', $name );
					$regex = '/' . $regex . '/';
					preg_match( $regex, $permission, $matches );
					if( count( $matches ) > 0 ){
						$communities[] = $matches[ 1 ];
					}
				}
			}
		}
		foreach( $communities as $community ){
			$restaurants = Restaurant::getRestaurantsByCommunity( $community );
			foreach ( $restaurants as $restaurant ) {
				$restaurants_ids[] = $restaurant->id_restaurant;
			}
		}
		return array_unique( $restaurants_ids );
		*/



	}

	public function getRestaurantsUserHasPermission(){
		return Crunchbutton_Restaurant::restaurantsUserHasPermission();
	}

	public function getRestaurantsUserHasPermissionToSeeTheirMetrics(){
		return Crunchbutton_Chart::restaurantsUserHasPermissionToSeeTheirMetrics();
	}

	public function getRestaurantsUserHasPermissionToSeeTheirOrders(){
		return Crunchbutton_Order::restaurantsUserHasPermissionToSeeTheirOrders();
	}

	public function getRestaurantsUserHasPermissionToSeeTheirTickets(){
		return Crunchbutton_Support::restaurantsUserHasPermissionToSeeTheirTickets();
	}

	public function getPermissionsByGroups(){
		return c::db()->get( "SELECT ap.*, g.name as group_name FROM admin_permission ap
										INNER JOIN admin_group ag ON ap.id_group = ap.id_group and ag.id_admin = {$this->id_admin}
										INNER JOIN `group` g ON g.id_group = ag.id_group ORDER BY group_name, permission ASC" );
	}

	public function getPermissionsByUser(){
		return c::db()->get( "SELECT * FROM admin_permission WHERE id_admin = {$this->id_admin}" );
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
		if( $this->id_admin ){
			if( !$this->_groups ){
				$this->_groups = Crunchbutton_Group::q( "SELECT g.* FROM `group` g INNER JOIN admin_group ag ON ag.id_group = g.id_group AND ag.id_admin = {$this->id_admin} ORDER BY name ASC" );
			}
			return $this->_groups;
		}
		return false;
	}

	public function removeGroups(){
		Cana::db()->query( "DELETE FROM `admin_group` WHERE id_admin = {$this->id_admin}" );
	}

	public function removeGroup( $id_group ){
		Cana::db()->query( "DELETE FROM `admin_group` WHERE id_admin = {$this->id_admin} AND id_group = {$id_group}" );
	}

	public function permissions(){
		if( !$this->_permissions ){
			$this->_permissions = c::db()->get( "SELECT * FROM admin_permission WHERE id_admin = {$this->id_admin}" );
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

	public function removePermissions(){
		c::db()->query( "DELETE FROM admin_permission WHERE id_admin = {$this->id_admin}" );
	}

	public function addPermissions( $permissions ){

		if( $permissions && is_array( $permissions ) ){
			foreach( $permissions as $key => $val ){
				if( !$this->hasPermission( $key ) ){
					$_permission = new Crunchbutton_Admin_Permission();
					$_permission->id_admin = $this->id_admin;
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

		$query .= " ORDER BY name ASC";

		$admins = self::q($query);
		return $admins;
	}

	public function getConfig( $key ){
		$config = Crunchbutton_Admin_Config::q( 'SELECT * FROM admin_config WHERE `key` = "' . $key . '" AND id_admin = ' . $this->id_admin );
		if( $config->id_admin_config ){
			return $config;
		}
		return false;
	}

	public function setConfig( $key, $value ){
		$config = $this->getConfig( $key );
		if( !$config->id_admin_config ){
			$config = new Crunchbutton_Admin_Config();
			$config->id_admin = $this->id_admin;
		}
		$config->key = $key;
		$config->value = $value;
		$config->save();
	}

	public function exports( $remove = [] ) {
		if (!$this->id_admin) {
			return ['name' => '', 'id_admin' => ''];
		}
		$permissions = [];
		$groups = [];
		$communities = [];

		if( $this->groups() ){
			foreach ($this->groups() as $group) {
				$groups[$group->id_group] = $group->name;
			}
		}

		if( $this->communitiesHeDeliveriesFor() ){
			foreach( $this->communitiesHeDeliveriesFor() as $community ){
				$communities[ $community->id_community ] = $community->name;
			}
		}

		if ($this->permission()->_permissions) {
			foreach ($this->permission()->_permissions as $group => $perms) {
				foreach ($perms as $key => $value) {
					if ($value) {
						$permissions[$key] = true;
					}
				}
			}
		}

		if ($this->permission()->_userPermission) {
			foreach ($this->permission()->_userPermission as $key => $value) {
				if ($value) {
					$permissions[$key] = true;
				} elseif ($permissions[$key]) {
					unset($permissions[$key]);
				}
			}
		}

		$ex = [
			'id_admin' => $this->id_admin,
			'login' => $this->login,
			'name' => $this->name,
			'phone' => $this->phone,
			'txt' => $this->txt,
			'email' => $this->email,
			'timezone' => $this->timezone,
			'testphone' => $this->testphone,
			'permissions' => $permissions,
			'groups' => $groups,
			'communities' => $communities,
			'active' => ( $this->active == 1 )
		];

		foreach( $remove as $rem ){
			unset( $ex[ $rem ] );
		}

		return $ex;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('admin')
			->idVar('id_admin')
			->load($id);
	}
}