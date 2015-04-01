<?php

class Crunchbutton_Admin extends Cana_Table_Trackchange {

	const CONFIG_RECEIVE_DRIVER_SCHEDULE_SMS_WARNING = 'schedule-sms';
	const CONFIG_VEHICLE_KEY = 'vehicle';
	const CONFIG_ORDER_PER_HOUR_KEY = 'orders-per-hour';

	const VEHICLE_BIKE = 'bike';
	const VEHICLE_CAR = 'car';

	public function inviteCode(){
		if( $this->id_admin && ( !$this->invite_code || $this->invite_code == '' ) ){
			$this->invite_code = Crunchbutton_User::inviteCodeGenerator();
			$this->save();
		}
		return $this->invite_code;
	}

	public function ssn_mask(){
		if( trim( $this->ssn() ) != '' ){
			return Crunchbutton_Admin_Info::SSN_MASK;
		}
		return '';
	}

	public function social_security_number( $ssn = false ){
		if( $ssn ){
			return Crunchbutton_Admin_Payment_Type::save_social_security_number( $this->id_admin, $ssn );
		}
		return Crunchbutton_Admin_Payment_Type::social_security_number( $this->id_admin );
	}

	public function save_social_security_number( $ssn ){
		return Crunchbutton_Admin_Payment_Type::save_social_security_number( $this->id_admin, $ssn );
	}

	// alias
	public function ssn( $ssn = false ){
		return $this->social_security_number( $ssn );
	}

	public function save_ssn( $ssn ){
		return $this->save_social_security_number( $this->id_admin, $ssn );
	}

	public static function byInviteCode( $code ){
		return Crunchbutton_Admin::q( 'SELECT * FROM admin WHERE UPPER( invite_code ) = UPPER("' . $code . '")' );
	}

	public function vehicle(){
		$vehicle = $this->getConfig( Cockpit_Admin::CONFIG_VEHICLE_KEY );
		if( $vehicle ){
			return $vehicle->value;
		}
		return Cockpit_Admin::vehicleDefault();
	}

	public function vehicleDefault(){
		return Cockpit_Admin::VEHICLE_CAR;
	}

	public function vehicleOptions(){
		return [ Cockpit_Admin::VEHICLE_CAR, Cockpit_Admin::VEHICLE_BIKE ];
	}

	public function saveVehicle( $vehicle ){
		$this->setConfig( Cockpit_Admin::CONFIG_VEHICLE_KEY, $vehicle );
	}

	public function ordersPerHour(){
		$orders = $this->getConfig( Cockpit_Admin::CONFIG_ORDER_PER_HOUR_KEY );
		if( $orders ){
			return $orders->value;
		}
		return null;
	}

	public function saveOrdersPerHour( $orders ){
		$this->setConfig( Cockpit_Admin::CONFIG_ORDER_PER_HOUR_KEY, $orders );
	}

	public static function login($login, $inactive = false) {
		$status = ( $inactive ? '' : 'and active = true' );
		return self::q('select * from admin where login = ? ' . $status . ' limit 1',[$login])->get(0);
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

	public function hasPaymentType(){
		$payment_type = $this->payment_type();
		if( $payment_type->balanced_id && $payment_type->balanced_bank ){
			return true;
		}
		return false;
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

	public function nameAbbr(){
		$name = explode( ' ', $this->name );
		$lastName = $name[ count( $name ) - 1 ];
		if( trim( $lastName ) != '' ){
			$lastName = ' ' . $lastName[0];
		} else {
			$lastName = '';
		}
		return $this->firstName() . $lastName;
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

	public function getByPhone( $phone, $activeOnly = false){
		return Crunchbutton_Admin::q("SELECT * FROM admin a WHERE ".($activeOnly ? 'active=true AND' : '')." (REPLACE( REPLACE( a.txt, ' ', '' ), '-', '' ) = '{$phone}' OR REPLACE( REPLACE( a.phone, ' ', '' ), '-', '' ) = '{$phone}') ORDER BY id_admin DESC LIMIT 1 ")->get(0);
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
		if (!isset($this->_timezone) && $this->timezone) {
			$this->_timezone = new DateTimeZone($this->timezone);
		} else {
			$this->_timezone = new DateTimeZone( c::config()->timezone );
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
		$notifications = Crunchbutton_Admin_Notification::q( "SELECT * FROM admin_notification WHERE id_admin = {$this->id_admin} AND active = true" );
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
		$notifications = Crunchbutton_Admin_Notification::q( "SELECT * FROM admin_notification WHERE id_admin = {$this->id_admin} AND active = true" );
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
				$this->_activeNotifications = Crunchbutton_Admin_Notification::q( "SELECT * FROM admin_notification WHERE id_admin = {$this->id_admin} AND active = true" );
			}
		}
		return $this->_activeNotifications;
	}

	public function restaurantsHeDeliveryFor(){
		return Restaurant::q("SELECT DISTINCT( r.id_restaurant ) id, r.* FROM restaurant r INNER JOIN notification n ON n.id_restaurant = r.id_restaurant AND n.type = '" . Crunchbutton_Notification::TYPE_ADMIN . "' AND n.active = true AND r.active = true AND n.id_admin = ?", [$this->id_admin]);
	}

	public function driversList( $search = '' ){

		$where = ( $search && trim( $search ) != '' ) ? ' AND a.name LIKE "%' . $search . '%"' : '';
		return Admin::q( 'SELECT a.* FROM admin a
												INNER JOIN (
													SELECT DISTINCT(id_admin) FROM (
													SELECT DISTINCT(a.id_admin) FROM admin a INNER JOIN admin_notification an ON a.id_admin = an.id_admin AND an.active = true
													UNION
													SELECT DISTINCT(a.id_admin) FROM admin a
														INNER JOIN admin_group ag ON ag.id_admin = a.id_admin
														INNER JOIN `group` g ON g.id_group = ag.id_group AND g.name LIKE "' . Crunchbutton_Group::DRIVER_GROUPS_PREFIX . '%"
														INNER JOIN admin_notification an ON a.id_admin = an.id_admin AND an.active = true
														) drivers
													)
											drivers ON drivers.id_admin = a.id_admin ' . $where . ' ORDER BY name ASC' );
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

													SELECT DISTINCT(a.id_admin) FROM admin a INNER JOIN admin_notification an ON a.id_admin = an.id_admin AND an.active = true
													UNION
													SELECT DISTINCT(a.id_admin) FROM admin a
														INNER JOIN admin_group ag ON ag.id_admin = a.id_admin
														INNER JOIN `group` g ON g.id_group = ag.id_group AND g.name LIKE "' . Crunchbutton_Group::DRIVER_GROUPS_PREFIX . '%"
														INNER JOIN admin_notification an ON a.id_admin = an.id_admin AND an.active = true
													UNION
													SELECT DISTINCT(a.id_admin) FROM admin a
														INNER JOIN admin_group ag ON ag.id_admin = a.id_admin
														INNER JOIN `group` g ON g.id_group = ag.id_group AND g.name = "' . Crunchbutton_Community::CUSTOMER_SERVICE_COMMUNITY_GROUP . '"
														) drivers
													)
											drivers ON drivers.id_admin = a.id_admin AND a.active = true ORDER BY name ASC' );
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

	public function communityDriverDelivery(){
		$adminCommunities = [];
		$groups = $this->groups();
		foreach ( $groups as $group ) {
			$communities = Crunchbutton_Community::communityByDriverGroup( $group->name );
			foreach( $communities as $community ){
				if( $community->active ){
					return Crunchbutton_Community::o( $community->id_community );
				}
			}
		}
		return false;
	}

	public function isDriver() {
		if (!isset($this->_isDriver)) {
			$query = 'SELECT COUNT(*) AS Total FROM admin_group ag INNER JOIN `group` g ON g.id_group = ag.id_group WHERE ag.id_admin = ? AND g.name LIKE "drivers-%" AND g.name !=?';
			$result = c::db()->get( $query, [$this->id_admin, Crunchbutton_Community::CUSTOMER_SERVICE_COMMUNITY_GROUP]);
			$this->_isDriver = ( $result->_items[0]->Total > 0 );
		}
		return $this->_isDriver;
	}

	public function isMarketingRep(){
		if (!isset($this->_isMarketingRep)) {
			$query = 'SELECT COUNT(*) AS Total FROM admin_group ag INNER JOIN `group` g ON g.id_group = ag.id_group WHERE ag.id_admin = ? AND type = ?';
			$result = c::db()->get( $query, [$this->id_admin, Crunchbutton_Group::TYPE_MARKETING_REP]);
			$this->_isMarketingRep = ( $result->_items[0]->Total > 0 );
		}
		return $this->_isMarketingRep;
	}

	public function isSupport( $onlyReturnTrueIfTheyAreWorking = false ) {
		if ( !isset( $this->_isSupport ) ) {
			$result = c::db()->get('SELECT COUNT(*) AS c FROM admin_group ag
																LEFT JOIN `group` g using (id_group)
																WHERE ag.id_admin = ?
																AND g.name= ?', [$this->id_admin, Config::getVal( Crunchbutton_Support::CUSTOM_SERVICE_GROUP_NAME_KEY)]);
			if( !$onlyReturnTrueIfTheyAreWorking ){
				$this->_isSupport = $result->get( 0 )->c ? true : false;
				return $this->_isSupport;
			}
			// Check if they are working based on their shift
			// https://github.com/crunchbutton/crunchbutton/issues/2638#issuecomment-64863807
			$shift = Crunchbutton_Community_Shift::shiftDriverIsCurrentWorkingOn( $this->id_admin );
			$this->_isSupport = ( $shift->count() && $shift->id_community_shift ) ? true : false;
		}
		return $this->_isSupport;
	}

	public function communitiesHeDeliveriesFor() {
		if (!isset($this->_communitiesHeDeliveriesFor)) {
			$this->_communitiesHeDeliveriesFor = Community::q('
				SELECT c.* FROM community c
				LEFT JOIN `group` g ON g.name=c.driver_group
				LEFT JOIN admin_group ag ON ag.id_group=g.id_group
				WHERE ag.id_admin=?
			', [$this->id_admin]);
		}
		return $this->_communitiesHeDeliveriesFor;
	}

	public function isWorking( $dt = null ){
		// Based on their shifts #2638
		$shift = Crunchbutton_Community_Shift::shiftDriverIsCurrentWorkingOn( $this->id_admin, $dt );
		if( !$shift ){
			return false;
		}
		return true;
	}

	public function getLastWorkedTimeHours(){
		$shift = $this->lastWorkedShift();
		if( $shift ){
			$now = new DateTime( 'now', $this->timezone() );
			$end = $shift->dateEnd()->get( 0 );
			$secs = Util::intervalToSeconds( $now->diff( $end ) );
			$hours = $secs / 60 / 60;
			return intval( $hours );
		}
		return false;
	}

	public function lastWorkedShift(){
		if( !$this->isWorking() ){
			$shift = Crunchbutton_Community_Shift::getLastWorkedShiftByAdmin( $this->id_admin );
			if( $shift->id_community_shift ){
				return $shift;
			}
		}
		return false;
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
		return Crunchbutton_Admin_Notification::q('SELECT * FROM admin_notification WHERE id_admin = ? ORDER BY '.$oderby, [$this->id_admin]);
	}

	public function getAllPermissionsName(){
		return c::db()->get('SELECT DISTINCT( ap.permission ) FROM admin_permission ap WHERE ap.id_admin = ? OR ap.id_group IN ( SELECT id_group FROM admin_group WHERE id_admin = ? )', [$this->id_admin, $this->id_admin]);
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
		return c::db()->get('SELECT ap.*, g.name as group_name FROM admin_permission ap
										INNER JOIN admin_group ag ON ap.id_group = ap.id_group and ag.id_admin = ?
										INNER JOIN `group` g ON g.id_group = ag.id_group ORDER BY group_name, permission ASC', [$this->id_admin]);
	}

	public function getPermissionsByUser(){
		return c::db()->get('SELECT * FROM admin_permission WHERE id_admin = ?', [$this->id_admin]);
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
				$this->_groups = Crunchbutton_Group::q('SELECT g.* FROM `group` g INNER JOIN admin_group ag ON ag.id_group = g.id_group AND ag.id_admin = ? ORDER BY name ASC', [$this->id_admin]);
			}
			return $this->_groups;
		}
		return false;
	}

	public function removeGroups(){
		Cana::db()->query('DELETE FROM `admin_group` WHERE id_admin = ?' , [$this->id_admin]);
	}

	public function removeGroup( $id_group ){
		Cana::db()->query('DELETE FROM `admin_group` WHERE id_admin = ? AND id_group = ?', [$this->id_admin, $id_group]);
	}

	public function permissions(){
		if( !$this->_permissions ){
			$this->_permissions = c::db()->get('SELECT * FROM admin_permission WHERE id_admin = ?', [$this->id_admin]);
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
		c::db()->query('DELETE FROM admin_permission WHERE id_admin = ?', [$this->id_admin]);
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

	public function config($key = null) {
		if (!isset($this->_config)) {
			$config = Crunchbutton_Admin_Config::q('SELECT * FROM admin_config WHERE id_admin=?', [$this->id_admin]);
			foreach ($config as $c) {
				$this->_config[$c->key] = $c;
			}
		}
		if ($key) {
			return $this->_config[$key] ? $this->_config[$key] : false;
		} else {
			return $this->_config;
		}
	}

	public function getConfig($key) {
		return $this->config($key);
	}

	public function setConfig( $key, $value, $exposed = 0 ){
		$config = $this->getConfig( $key );
		if( !$config->id_admin_config ){
			$config = new Crunchbutton_Admin_Config();
			$config->id_admin = $this->id_admin;
		}
		$config->key = $key;
		$config->value = self::formatPref($key, $value);

		$config->exposed = $exposed;
		$config->save();
	}

	public function exports( $remove = [] ) {
		if (!$this->id_admin) {
			return ['name' => '', 'id_admin' => ''];
		}
		$permissions = [];
		$groups = [];
		$communities = [];

		if( !in_array( 'groups', $remove ) ){
			if( $this->groups() ){
				foreach ($this->groups() as $group) {
					$groups[$group->id_group] = $group->name;
				}
			}
		}

		if( !in_array( 'communities', $remove ) ){
			if( $this->communitiesHeDeliveriesFor() ){
				foreach( $this->communitiesHeDeliveriesFor() as $community ){
					$communities[ $community->id_community ] = $community->name;
				}
			}
		}

		if( !in_array( 'permissions', $remove ) ){
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
			'vehicle' => $this->vehicle(),
			'communities' => $communities,
			'active' => ( $this->active == 1 )
		];

		$cfg = $this->config();
		$ex['prefs'] = [];

		if ($cfg) {
			foreach ($cfg as $config) {
				if ($config->exposed) {
					$ex['prefs'][$config->key] = self::formatPref($config->key, $config->value, true);
				}
			}
		}

		foreach( $remove as $rem ){
			unset( $ex[ $rem ] );
		}

		$community_delivery = $this->communityDriverDelivery();
		if( $community_delivery->id_community ){
			$ex[ 'id_community' ] = $community_delivery->id_community;
		}
		return $ex;
	}

	private static function _isBoolPref($pref) {
		$boolprefs = ['demo', 'notification-desktop-support-all'];
		return in_array($pref, $boolprefs);
	}

	public static function formatPref($key, $value, $export = false) {
		if ($export) {
			return self::_isBoolPref($key) ? ($value == '1' ? true : false) : $value;
		} else {
			return self::_isBoolPref($key) ? ($value == 'true' || $value === true ? '1' : '0') : $value;
		}
	}

	//Last Shift
	public function avgDeliveryTimeLastShift( $id_admin ){
		$shift = Crunchbutton_Community_Shift::getLastWorkedShiftByAdmin( $id_admin );
		return Admin::avgDeliveryTimeByShift( $id_admin,  $shift );
	}

	public function numberOfDeliveredOrdersLastShift( $id_admin ){
		$shift = Crunchbutton_Community_Shift::getLastWorkedShiftByAdmin( $id_admin );
		return Admin::numberOfDeliveredOrdersByShift( $id_admin,  $shift );
	}

	public function revenueLastWorkedShift( $id_admin ){
		$shift = Crunchbutton_Community_Shift::getLastWorkedShiftByAdmin( $id_admin );
		return Admin::revenueByShift( $id_main, $shift );
	}
	//*****

	//Current Shift
	public function avgDeliveryTimeCurrentShift( $id_admin ){
		$shift = Crunchbutton_Community_Shift::getCurrentShiftByAdmin( $id_admin );
		return Admin::avgDeliveryTimeByShift( $id_admin, $shift );
	}

	public function numberOfDeliveredOrdersCurrentShift( $id_admin ){
		$shift = Crunchbutton_Community_Shift::getCurrentShiftByAdmin( $id_admin );
		return Admin::numberOfDeliveredOrdersByShift( $id_admin, $shift );
	}

	public function revenueCurrentShift( $id_admin ){
		$shift = Crunchbutton_Community_Shift::getCurrentShiftByAdmin( $id_admin );
		return Admin::revenueByShift( $id_admin, $shift );
	}

	//*****

	public function revenueByShift( $id_admin, $shift ){
		if( $shift->id_community_shift ){
			$start = $shift->dateStart( c::config()->timezone )->format( 'Y-m-d H:i:s' );
			$end = $shift->dateEnd( c::config()->timezone )->format( 'Y-m-d H:i:s' );
			$orders = Order::revenueByAdminPeriod( $id_admin, $start, $end);
			$revenue = 0;
			foreach($orders as $order){
				$revenue = $revenue + $order->deliveryFee() + $order->tip();
			}
			return $revenue;
			//$orders = Crunchbutton_Order_action::ordersDeliveryByAdminPeriod( $id_admin, $start, $end );
		}
		return 0;
	}

	public function numberOfDeliveredOrdersByShift( $id_admin, $shift ){
		if( $shift->id_community_shift ){
			$start = $shift->dateStart( c::config()->timezone )->format( 'Y-m-d H:i:s' );
			$end = $shift->dateEnd( c::config()->timezone )->format( 'Y-m-d H:i:s' );
			$orders = Crunchbutton_Order_Action::ordersDeliveryByAdminPeriod( $id_admin, $start, $end );
			return $orders->count();
		}
		return 0;
	}

	public function avgDeliveryTimeByShift( $id_admin, $shift ){
		if( $shift->id_community_shift ){
			$start = $shift->dateStart( c::config()->timezone )->format( 'Y-m-d H:i:s' );
			$end = $shift->dateEnd( c::config()->timezone )->format( 'Y-m-d H:i:s' );
			$orders = Crunchbutton_Order_Action::ordersDeliveryByAdminPeriod( $id_admin, $start, $end );
			$delivery_time = 0;
			$delivered_orders = 0;
			foreach( $orders as $order ){
				$minutes = $order->minutesToDelivery();
				if( $minutes > 0 ){
					$delivery_time += $minutes;
					$delivered_orders++;
				}
			}
			if( $delivery_time && $delivered_orders ){
				return round( $delivery_time / $delivered_orders );
			}
		}
		return 0;
	}

	public function setPush($id, $os = 'ios') {
		$os = $os == 'ios' ? Crunchbutton_Admin_Notification::TYPE_PUSH_IOS : Crunchbutton_Admin_Notification::TYPE_PUSH_ANDROID;

		$notifications = Admin_Notification::q('
			SELECT * FROM admin_notification
			WHERE
				id_admin=?
				AND `type`=?
		', [$this->id_admin, $os]);
		foreach($notifications as $n) {
			if ($n->value == $id) {
				$exists = true;
				if (!$n->active) {
					$n->active = 1;
					$n->save();
				}
			}
		}
		if (!$exists) {
			$n = new Admin_Notification([
				'active' => 1,
				'value' => $id,
				'type' => $os,
				'id_admin' => $this->id_admin
			]);
			$n->save();
		}
	}

	public function note(){
		return Crunchbutton_Admin_Note::lastNoteByAdmin( $this->id_admin )->get( 0 );
	}

	public function addNote( $text ){
		$shouldAdd = false;
		$lastNote = $this->note();
		if( !$lastNote->id_admin_note || trim( $lastNote->text ) != trim( $text ) ){
			$note = new Crunchbutton_Admin_Note;
			$note->id_admin = $this->id_admin;
			$note->id_admin_added = c::admin()->id_admin;
			$note->text = trim( $text );
			$note->date = date( 'Y-m-d H:i:s' );
			$note->save();
		}
	}

	public function driver_info(){
		$driver_info = Cockpit_Driver_Info::byAdmin( $this->id_admin );
		if( !$driver_info->id_driver_info ){
			$driver_info = new Cockpit_Driver_Info;
			$driver_info->id_admin = $this->id_admin;
			$driver_info->save();
		} else {
			$driver_info = $driver_info->get( 0 );
		}
		return $driver_info;
	}

	public function author(){
		if( !$this->_author && $this->id_admin_author ){
			$this->_author = Admin::o( $this->id_admin_author );
		}
		return $this->_author;
	}

	public function save() {
		$this->phone = Phone::clean($this->phone);
		$this->txt = Phone::clean($this->txt);
		$this->testphone = Phone::clean($this->testphone);
		// if it is a new record saves the author
		if( !$this->id_admin && c::admin()->id_admin ){
			$this->id_admin_author = c::admin()->id_admin;
		}
		return parent::save();
	}

	public function getMarketingRepGroups(){
		$_groups = [];
		$groups = $this->groups();
		foreach( $groups as $group ){
			if( $group->type == Crunchbutton_Group::TYPE_MARKETING_REP ){
				return $group->id_community;
			}
		}
	}

	public function hasPexCard(){
		$payment_type = $this->payment_type();
		return ( $payment_type->using_pex ? true : false );
	}

	// return the last added pexcard
	public function pexcard(){
		return Cockpit_Admin_Pexcard::q( 'SELECT * FROM admin_pexcard WHERE id_admin = ? ORDER BY id_admin_pexcard DESC', [$this->id_admin])->get( 0 );
	}

	public function dateTerminated(){
		if( $this->date_terminated ){
			return new DateTime( $this->date_terminated, new DateTimeZone( c::config()->timezone ) );
		}
		return false;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this->changeOptions([
			'author_id' => 'id_author'
		]);

		$this
			->table('admin')
			->idVar('id_admin')
			->load($id);
	}
}