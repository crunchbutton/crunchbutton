<?php

class Crunchbutton_Admin extends Cana_Table_Trackchange {

	const CONFIG_RECEIVE_DRIVER_SCHEDULE_SMS_WARNING = 'schedule-sms';
	const CONFIG_VEHICLE_KEY = 'vehicle';
	const CONFIG_ORDER_PER_HOUR_KEY = 'orders-per-hour';

	const VEHICLE_BIKE = 'bike';
	const VEHICLE_CAR = 'car';
	const VEHICLE_ROLLERBLADES = 'rollerblades';
	const VEHICLE_RICKSHAW = 'rickshaw';
	const VEHICLE_SKATEBOARD = 'skateboard';

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

	public function hasSSN() {
		if (!$this->id_admin) {
			return false;
		}
		$id = Crunchbutton_Admin_Info::q('select id_admin_info from admin_info where `key`=? and id_admin=?',['ssn', $this->id_admin])->get(0);
		return $id->id_admin_info ? true : false;
	}

	public function save_ssn( $ssn ){
		return $this->save_social_security_number( $this->id_admin, $ssn );
	}

	public static function byInviteCode( $code ){
		return Crunchbutton_Admin::q( 'SELECT * FROM admin WHERE UPPER( invite_code ) = UPPER(?)', [ $code ] );
	}

	public function stopHelpOutNotification(){
		$driverInfo = Cockpit_Driver_Info::byAdmin( $this->id_admin )->get( 0 );
		if( $driverInfo->id_admin ){
			return $driverInfo->stopHelpOutNotification();
		}
		return false;
	}

	public function couldReceiveHelpOutNotification(){
		$driverInfo = Cockpit_Driver_Info::byAdmin( $this->id_admin )->get( 0 );
		if( $driverInfo->id_admin ){
			return $driverInfo->couldReceiveHelpOutNotification();
		}
		return false;
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
		return [ 	Cockpit_Admin::VEHICLE_CAR,
					Cockpit_Admin::VEHICLE_BIKE,
					Cockpit_Admin::VEHICLE_ROLLERBLADES,
					Cockpit_Admin::VEHICLE_RICKSHAW,
					Cockpit_Admin::VEHICLE_SKATEBOARD ];
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
		return self::q('select * from admin where login = ? ' . $status . ' limit 1',[urldecode($login)])->get(0);
	}

	public function publicExports() {
		return [
			'name' => $this->name,
			'id_admin' => $this->id_admin
		];
	}

	public function paymentType(){
		return $this->payment_type();
	}

	public function payment_type(){
		if (!isset($this->_payment_type)) {
			$this->_payment_type = Crunchbutton_Admin_Payment_Type::byAdmin($this->id_admin);
		}
		return $this->_payment_type;
	}

	public function hasPaymentType(){
		$payment_type = $this->payment_type();
		if( $payment_type->stripe_id && $payment_type->stripe_account_id ){
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
		return Admin::q( "SELECT a.* FROM admin a INNER JOIN phone p ON a.id_phone = p.id_phone WHERE p.phone = ? ".($activeOnly ? 'AND a.active=true ' : '')." ORDER BY id_admin DESC LIMIT 1", [ Phone::clean( $phone ) ] )->get( 0 );
	}

	public function getByPhoneSetup( $phone ){
		return Admin::q( "SELECT * FROM admin a WHERE a.phone = '{$phone}' AND ( a.pass IS NULL OR a.pass = '' ) ORDER BY id_admin DESC LIMIT 1 " );
	}

	public function getCSAdminByPhone( $phone ){
		die('@deprecated');
		$group = Crunchbutton_Group::byName( Config::getVal( Crunchbutton_Support::CUSTOM_SERVICE_GROUP_NAME_KEY ) );
		return Crunchbutton_Admin::q( "SELECT * FROM admin a INNER JOIN admin_group ag ON ag.id_admin = a.id_admin AND ag.id_group = '" . $group->id_group ."' WHERE REPLACE( REPLACE( a.txt, ' ', '' ), '-', '' ) = '{$phone}' OR REPLACE( REPLACE( a.phone, ' ', '' ), '-', '' ) = '{$phone}' ORDER BY a.id_admin DESC LIMIT 1 " );
	}

	public function checkIfThePhoneBelongsToAnAdmin( $phone ){
		return Crunchbutton_Admin::q( "SELECT * FROM admin a INNER JOIN phone p ON a.id_phone = p.id_phone AND p.phone = ?", [ Phone::clean( $phone ) ] );
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
		if( !$this->_phone ) {
			$phone = Phone::o( $this->id_phone );
			if( !$phone->id_phone ){
				$phone = Phone::byPhone( $this->phone );
				$this->id_phone = $phone->id_phone;
				$this->save();
			}
			$phone = $phone->phone;
			$this->_phone = Phone::formatted( $phone );
		}
		return $this->_phone;
	}

	public function getTxtNumber(){
		if( $this->txt ){
			return $this->txt;
		}
		$notifications = Crunchbutton_Admin_Notification::q( "SELECT * FROM admin_notification WHERE id_admin = ? AND active = true", [$this->id_admin] );
		foreach( $notifications as $notification ){
			if( $notification->type == Crunchbutton_Admin_Notification::TYPE_SMS ){
				return $notification->value;
			}
		}
		return false;
	}

	public function getPhoneNumber(){
		if( $this->id_phone ){
			return $this->phone();
		}
		$notifications = Crunchbutton_Admin_Notification::q( "SELECT * FROM admin_notification WHERE id_admin = ? AND active = true", [$this->id_admin] );
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
				$this->_activeNotifications = Crunchbutton_Admin_Notification::q( "SELECT * FROM admin_notification WHERE id_admin = ? AND active = true", [$this->id_admin] );
			}
		}
		return $this->_activeNotifications;
	}

	public function restaurantsHeDeliveryFor(){
		return Restaurant::q("SELECT DISTINCT( r.id_restaurant ) id, r.* FROM restaurant r INNER JOIN notification n ON n.id_restaurant = r.id_restaurant AND n.type = ? AND n.active = true AND r.active = true AND n.id_admin = ?", [Crunchbutton_Notification::TYPE_ADMIN, $this->id_admin]);
	}

	public function driversList( $search = '' ){

		$where = ( $search && trim( $search ) != '' ) ? ' AND a.name LIKE "%' . $search . '%"' : '';
		$admins = Admin::q( 'SELECT * FROM admin a WHERE a.active = true ' . $where );
		$drivers = [];
		foreach( $admins as $admin ){
			if( $admin->isDriver() ){
				$drivers[] = $admin;
			}
		}
		return $drivers;
	}

	public function drivers(){
		return Admin::q( 'SELECT a.* FROM admin a
												INNER JOIN (
													SELECT DISTINCT(id_admin) FROM (

													SELECT DISTINCT(a.id_admin) FROM admin a INNER JOIN admin_notification an ON a.id_admin = an.id_admin AND an.active = true
													UNION
													SELECT DISTINCT(a.id_admin) FROM admin a
														INNER JOIN admin_group ag ON ag.id_admin = a.id_admin
														INNER JOIN `group` g ON g.id_group = ag.id_group AND g.name LIKE ?
														INNER JOIN admin_notification an ON a.id_admin = an.id_admin AND an.active = true
													UNION
													SELECT DISTINCT(a.id_admin) FROM admin a
														INNER JOIN admin_group ag ON ag.id_admin = a.id_admin
														INNER JOIN `group` g ON g.id_group = ag.id_group AND g.name = ?
														) drivers
													)
											drivers ON drivers.id_admin = a.id_admin AND a.active = true ORDER BY name ASC', [Crunchbutton_Group::DRIVER_GROUPS_PREFIX.'%', Crunchbutton_Community::CUSTOMER_SERVICE_COMMUNITY_GROUP]);
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
			if( $group->type == Crunchbutton_Group::TYPE_DRIVER ){
				$community = $group->community();
				if( $community->id_community ){
					$restaurants = $community->getRestaurants();
					foreach( $restaurants as $restaurant ){
						if( $restaurant->delivery_service ){
							$deliveryFor[ $restaurant->id_restaurant ] = $restaurant->id_restaurant;
						}
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
			if( $group->id_community && $group->type == Crunchbutton_Group::TYPE_DRIVER ){
				return $group->community();
			}
		}
		return false;
	}

	public function hasCSPermissionForCommunity($id_community){
		$hasPermission = false;
		$communities = $this->communitiesDriverDelivery();
		foreach($communities as $_community){
			if ($id_community == $_community->id_community) {
				$hasPermission = true;
			}
		}
		return $hasPermission;
	}

	public function communitiesDriverDelivery(){
		$adminCommunities = [];
		$groups = $this->groups();
		foreach ( $groups as $group ) {
			if( $group->id_community && $group->type == Crunchbutton_Group::TYPE_DRIVER ){
				$adminCommunities[] = $group->community();
			}
		}
		return $adminCommunities;
	}

	public function amountPerOrder( $id_community = null ){
		$paymentType = $this->paymentType();
		return $paymentType->amountPerOrder( $id_community );
	}

	public function is( $type ){
		$query = 'SELECT * FROM admin_group ag WHERE ag.id_admin = ? AND ag.type = ? ORDER BY id_admin_group DESC LIMIT 1';
		$adminGroup = Admin_Group::q( $query, [ $this->id_admin, $type ] )->get( 0 );
		if( $adminGroup->id_admin_group ){
			return true;
		}
		return false;
	}

	public function isCommunityCS(){
		return $this->isCommunitySupport();
	}

	public function isCommunitySupport(){
		if (!isset($this->_isCommunitySupport)) {
			$this->_isCommunitySupport = $this->is( Crunchbutton_Group::TYPE_COMMUNITY_CS );
		}
		return $this->_isCommunitySupport;
	}

	public function isDriver() {
		if (!isset($this->_isDriver)) {
			$this->_isDriver = $this->is( Crunchbutton_Group::TYPE_DRIVER );
		}
		return $this->_isDriver;
	}

	public function isBrandRepresentative(){
		if (!isset($this->_isBrandRepresentative)) {
			$this->_isBrandRepresentative = $this->is( Crunchbutton_Group::TYPE_BRAND_REPRESENTATIVE );
		}
		return $this->_isBrandRepresentative;
	}

	// Legacy
	public function isMarketingRep(){
		return $this->isBrandRepresentative();
	}

	// Legacy
	public function isCampusManager(){
		return $this->isCommunityManager();
	}

	public function isCommunityManager(){
		if (!isset($this->_isCommunityManager)) {
			$this->_isCommunityManager = $this->is( Crunchbutton_Group::TYPE_COMMUNITY_MANAGER );
		}
		return $this->_isCommunityManager;
	}

	public function isSupport( $onlyReturnTrueIfTheyAreWorking = false ) {
		if( !$onlyReturnTrueIfTheyAreWorking ){
			return $this->is( Crunchbutton_Group::TYPE_SUPPORT );
		} else {
			if( $this->isSupport() && Crunchbutton_Community_Shift::shiftDriverIsCurrentWorkingOn( $this->id_admin, null, Crunchbutton_Community::CUSTOMER_SERVICE_ID_COMMUNITY ) ){
				return true;
			}
		}
		return false;
	}

	public function marketingGroups(){
		$mkt = [];
		$groups = $this->groups();
		foreach( $groups as $group ){
			if( $group->type == Crunchbutton_Group::MARKETING_REP_GROUP ){
				$mkt[ $group->name ] = $group->description;
			}
		}
		return $mkt;
	}

	public function campusManagerGroups(){
		$cm = [];
		$groups = $this->groups();
		foreach( $groups as $group ){
			if( $group->type == Crunchbutton_Group::CAMPUS_MANAGER_GROUP ){
				$cm[ $group->name ] = $group->description;
			}
		}
		return $cm;
	}

	public function communitiesHeDeliveriesFor() {
		if (!isset($this->_communitiesHeDeliveriesFor)) {
			$this->_communitiesHeDeliveriesFor = Community::q('
				SELECT c.* FROM community c INNER JOIN admin_group ag ON ag.id_group=c.id_driver_group WHERE ag.id_admin=? AND c.active = true
			', [$this->id_admin]);
		}
		return $this->_communitiesHeDeliveriesFor;
	}

	public function isWorking( $dt = null, $id_community = null, $checkIfTheyCheckedIn = false ){
		// Based on their shifts and community #2638
		$shift = Crunchbutton_Community_Shift::shiftDriverIsCurrentWorkingOn( $this->id_admin, $dt, $id_community, $checkIfTheyCheckedIn );
		if( !$shift ){
			return false;
		}
		return true;
	}

	// this means that the driver opened the community
	public function isWorkingOnExtraShifts( $dt = null, $id_community = null, $checkIfTheyCheckedIn = false ){
		$shift = Crunchbutton_Community_Shift::shiftDriverIsCurrentWorkingOn( $this->id_admin, $dt, $id_community, $checkIfTheyCheckedIn );
		if( $shift && $shift->id_driver && $shift->created_by_driver ){
			return true;
		}
		return false;
	}

	public function openedCommunity(){
		return $this->isWorkingOnExtraShifts();
	}

	public function getLastWorkedTimeHours( $now = 'now' ){
		$shift = $this->lastWorkedShift( $now );
		if ($shift){
			$now = new DateTime( 'now', $this->timezone() );
			$end = $shift->dateEnd()->get( 0 );
			if ($end) {
				$secs = Util::intervalToSeconds( $now->diff( $end ) );
				$hours = $secs / 60 / 60;
			} else {
				$hours = 0;
			}
			return floatval( $hours );
		}
		return false;
	}

	public function lastWorkedShift( $now = 'now' ){
		if( !$this->isWorking( $now ) ){
			$shift = Crunchbutton_Community_Shift::getLastWorkedShiftByAdmin( $this->id_admin, $now );
			if( $shift->id_community_shift ){
				return $shift;
			}
		}
		return false;
	}

	public function workingHoursWeek(){
		die ('#5430 deprecated');
		/*

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
		*/
	}

	public function getNotifications( $oderby = 'active DESC, id_admin_notification DESC' ){
		return Crunchbutton_Admin_Notification::q('SELECT * FROM admin_notification WHERE id_admin = ? ORDER BY '.$oderby, [$this->id_admin]);
	}

	public function getAllPermissionsName(){
		return c::db()->get('
			SELECT DISTINCT( ap.permission )
			FROM admin_permission ap
			WHERE (ap.id_admin = ? and ap.allow = true)
			OR ap.id_group IN (
				SELECT id_group
				FROM admin_group
				WHERE
					id_admin = ?
					and allow = true
			)
		', [$this->id_admin, $this->id_admin]);
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
				$this->_groups = Crunchbutton_Group::q('SELECT g.* FROM `group` g INNER JOIN admin_group ag ON ag.id_group = g.id_group AND ag.id_admin = ? LEFT JOIN community c ON c.id_community = g.id_community AND c.active = true ORDER BY name ASC', [$this->id_admin]);
			}
			return $this->_groups;
		}
		return false;
	}

	public function removeGroups(){
		Crunchbutton_Admin_Group::q( 'SELECT * FROM `admin_group` WHERE id_admin = ?', [$this->id_admin] )->delete();
	}

	public function removeGroup( $id_group ){
		Cana::dbWrite()->query('DELETE FROM `admin_group` WHERE id_admin = ? AND id_group = ?', [$this->id_admin, $id_group]);
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
			if( $_permission->permission == $permission && $_permission->allow){
				return true;
			}
			if( $useRegex ){
				if( preg_match( $permission, $_permission->permission )  && $_permission->allow){
					return true;
				}
			}
		}
		return false;
	}

	public function removePermissions(){
		c::dbWrite()->query('DELETE FROM admin_permission WHERE id_admin = ?', [$this->id_admin]);
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

	public function areSettlementDocsOk(){
		return Cockpit_Driver_Document_Status::areSettlementDocsOk( $this->id_admin );
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

					$description = ( $group->description ) ? $group->description : $group->name;
					$groups_description[$group->id_group] = trim( $description );
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

		$paymentType = $this->paymentType();

		$ex = [
			'id_admin' => $this->id_admin,
			'login' => $this->login,
			'name' => $this->name,
			'phone' => $this->phone(),
			'txt' => $this->txt,
			'email' => $this->email,
			'timezone' => $this->timezone,
			'testphone' => $this->testphone,
			'permissions' => $permissions,
			'groups' => $groups,
			'groups_description' => $groups_description,
			'vehicle' => $this->vehicle(),
			'communities' => $communities,
			'active' => $this->active,
			'payment_type' => $paymentType->payment_type,
			'show_credit_card_tips' => $this->showCreditCardTips(),
			'show_delivery_fees' => $this->showDeliveryFees(),
		];

		if( $paymentType->payment_type == Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS ||
		 		$paymentType->payment_type == Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS_WITHOUT_TIPS ||
		 		$paymentType->payment_type == Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_MAKING_WHOLE ){
			$ex[ 'hour_rate' ] = intval( $paymentType->hour_rate );
		}

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

		// export info is the user has notification auth
		$ex[ 'android_push' ] = Crunchbutton_Admin_Notification::adminHasNotification( $this->id_admin, Crunchbutton_Admin_Notification::TYPE_PUSH_ANDROID );
		$ex[ 'ios_push' ] = Crunchbutton_Admin_Notification::adminHasNotification( $this->id_admin, Crunchbutton_Admin_Notification::TYPE_PUSH_IOS );

		$community_delivery = $this->communityDriverDelivery();
		if( $community_delivery->id_community ){
			$ex[ 'id_community' ] = $community_delivery->id_community;
		}
		return $ex;
	}

	public function community(){
		if( !$this->_community ){
			$this->_community = $this->communityDriverDelivery();
		}
		return $this->_community;
	}

	public function hasCommunityToOpen(){
		if( $this->isDriver() ){
			$communities = $this->driverCommunities();
			foreach( $communities as $community ){
				if( $community->drivers_can_open ){
					return true;
				}
			}
		}
		return false;
	}

	public function hasCommunityToClose(){
		if( $this->isDriver() ){
			$communities = $this->driverCommunities();
			foreach( $communities as $community ){
				if( $community->drivers_can_close ){
					return true;
				}
			}
		}
		return false;
	}

	public function driverCommunities(){
		$communities = [];
		$groups = $this->groups();
		foreach ( $groups as $group ) {
			if( $group->id_community && $group->type == Crunchbutton_Group::TYPE_DRIVER ){
				$community = $group->community();
				if( $community->id_community != Crunchbutton_Community::CUSTOMER_SERVICE_ID_COMMUNITY ){
					$communities[] = $community;
				}
			}
		}
		return $communities;
	}

	// #5480
	public function showCreditCardTips(){
		$paymentType = $this->paymentType();
		return ( $paymentType->payment_type != Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS_WITHOUT_TIPS );
	}

	// #5480
	public function showDeliveryFees(){
		$paymentType = $this->paymentType();
		return ( 	$paymentType->payment_type != Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS &&
							$paymentType->payment_type != Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS_WITHOUT_TIPS );
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

		Cockpit_Driver_Log::enabledPush();

	}

	public function shiftsCurrentAssigned(){
		$firstDay = $this->firstDayOfWeek();
		$from = $firstDay->format( 'Y-m-d' );
		$firstDay->modify( '+6 days' );
		$to = $firstDay->format( 'Y-m-d' );
		return Crunchbutton_Admin_Shift_Assign::shiftsByAdminPeriod( $this->id_admin, $from, $to );
	}

	public function firstDayOfWeek(){
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone  ) );
		if( $now->format( 'l' ) == 'Thursday' ){
			$thursday = $now;
		} else {
			$thursday = new DateTime( 'last thursday', new DateTimeZone( c::config()->timezone  ) );
		}
		return $thursday;
	}

	public function shiftCurrentStatus(){
		$firstDay = $this->firstDayOfWeek();
		$year = $firstDay->format( 'Y' );
		$week = $firstDay->format( 'W' );
		return Crunchbutton_Admin_Shift_Status::getByAdminWeekYear( $this->id_admin, $week, $year );
	}

	public function lastNote(){
		return $this->note();
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

	public function save($new = false) {

		if( $this->phone ){
			$this->phone = Phone::clean($this->phone);
			$this->txt = Phone::clean($this->txt);
			$this->testphone = Phone::clean($this->testphone);
			$phone = Phone::byPhone( $this->phone );
			$this->id_phone = $phone->id_phone;
		}

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


	public function hasPaymentInfo( $processor = null ){

		if( !$processor ){
			$processor = Crunchbutton_Payment::processor();
		}

		$paymentType = $this->paymentType();
		if( $paymentType->stripe_id && $paymentType->stripe_account_id ){
			return true;
		}
		return false;
	}

	public function hasResource( $type = 'page' ){
		$_resources = Crunchbutton_Resource::byCommunity( 'all' );
		if( $_resources ){
			foreach( $_resources as $resource ){
				return true;
			}
		}

		$resources = [];

		$driver = c::user();
		$groups = $driver->groups();
		if ($groups) {
			foreach ( $groups as $group ) {
				if( $group->id_community ){
					$_resources = Crunchbutton_Resource::byCommunity( $group->id_community );
					if( $_resources ){
						foreach( $_resources as $resource ){
							if( $resource->active ){
								if( $type == 'page' ){
									if( $resource->page ){
										return true;
									}
								} else if( $type == 'side' ){
									if( $resource->side ){
										$resources[] = [ 'name' => $resource->name, 'url' => $resource->download_url() ];
									}
								}
							}
						}
					}
				}
			}
		}
		if( count( $resources ) ){
			return $resources;
		}
		return false;
	}

	public function sendTextAboutSchedule(){
		$receiveSMS = $this->getConfig( Crunchbutton_Admin::CONFIG_RECEIVE_DRIVER_SCHEDULE_SMS_WARNING );
		if( $receiveSMS->id_admin_config ){
			return $receiveSMS->value;
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
