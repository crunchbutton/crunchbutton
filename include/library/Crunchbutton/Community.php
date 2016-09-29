<?php

class Crunchbutton_Community extends Cana_Table_Trackchange {

	const CUSTOMER_SERVICE_ID_COMMUNITY = 92;
	const COMMUNITY_TEST = 6;
	const CUSTOMER_SERVICE_COMMUNITY_GROUP = 'support';
	const AUTO_SHUTDOWN_COMMUNITY_LOGIN = 'autoshutdowncommunity';

	const TITLE_CLOSE_ALL_RESTAURANTS = 'Close All Restaurants';
	const TITLE_CLOSE_3RD_PARY_RESTAURANTS = 'Close 3rd Party Delivery Restaurants';
	const TITLE_CLOSE_AUTO_CLOSED = 'Auto Closed';

	const DRIVER_OPEN_COMMUNITY_ERROR_COMMUNITY = 1;
	const DRIVER_OPEN_COMMUNITY_ERROR_SHIFT_HOURS = 2;
	const DRIVER_OPEN_COMMUNITY_ERROR_CREATING_SHIFT = 3;
	const DRIVER_OPEN_COMMUNITY_ERROR_ASSIGNING_SHIFT = 4;

	const PREORDER_MINUTES_AFTER_COMMUNITY_OPEN_DEFAULT = 60;

	public static function all($force = null) {
		$ip = preg_replace('/[^0-9\.]+/','',c::getIp());
		$force = preg_replace('/[^a-z\-]+/','',$force);
		$keys = ['ip' => $ip];

		if ($force) {
			$forceq = ' OR (community.permalink=:force) ';
			$keys['force'] = $force;
		}

		$q = '
			select community.* from community
			left join community_ip on community_ip.id_community=community.id_community
			where
				community.active=true
				AND (
					( community.private=false )
					OR
					(community.private=true AND community_ip.ip=:ip)
					'.$forceq.'
				)
			group by community.id_community
			order by name
		';

		return self::q($q, $keys);
	}

	public function driversHelpOut(){
		$list = [];
		$drivers = $this->getDriversOfCommunity();
		foreach( $drivers as $driver ){
			if( $driver->active && !$driver->isWorking() ){
				$info = Cockpit_Driver_Info::byAdmin( $driver->id_admin );
				if( $info->down_to_help_out ){
					$list[] = $driver;
				}
			}
		}
		return $list;
	}

	public function restaurantByLoc() {
		if (!isset($this->_restaurantsByLoc)) {
			$this->_restaurantsByLoc = Restaurant::byRange([
				'lat' => $this->loc_lat,
				'lon' => $this->loc_lon,
				'range' => $this->range,
			]);
		}
		return $this->_restaurantsByLoc;
	}

	public function save($new = false){

		// close logs
		if( $this->id_community ){
			$current = Community::q( 'SELECT * FROM community WHERE id_community = ? ', [ $this->id_community ] )->get( 0 );
			$keys = [ Cockpit_Community_Status_Log::TYPE_ALL_RESTAURANTS, Cockpit_Community_Status_Log::TYPE_3RD_PARY_DELIRERY_RESTAURANTS, Cockpit_Community_Status_Log::TYPE_AUTO_CLOSED ];
			$newKeys = $this->properties();
			$oldKeys = $current->properties();

			foreach ( $keys as $key ) {
				Log::debug( [ 'key' => $key,
											'old' => $oldKeys[ $key ],
											'new' => $newKeys[ $key ],
											'_new' => ( strval( $newKeys[ $key ] ) ? true : false ),
											'_old' => ( strval( $oldKeys[ $key ] ) ? true : false ),
											'type' => 'close-log'
											 ] );

				$newKeys[ $key ] = strval( $newKeys[ $key ] ) ? true : false;
				$oldKeys[ $key ] = strval( $oldKeys[ $key ] ) ? true : false;
				if( $newKeys[ $key ] !== $oldKeys[ $key ] ){
					$close = $newKeys[ $key ];
					$params = [ 'type' => $key, 'close' => $close, 'properties' => $newKeys ];
					Cockpit_Community_Status_Log::register( $params );
				}
			}

			$note_keys = [ Cockpit_Community_Status_Log::NOTE_ALL_RESTAURANTS, Cockpit_Community_Status_Log::NOTE_3RD_PARY_DELIRERY_RESTAURANTS, Cockpit_Community_Status_Log::NOTE_AUTO_CLOSED ];
			foreach ( $keys as $key ) {
				// if it is close, check if the message was changed
				$newKeys[ $key ] = strval( $newKeys[ $key ] ) ? true : false;
				$oldKeys[ $key ] = strval( $oldKeys[ $key ] ) ? true : false;
				if( $oldKeys[ $key ] && !$newKeys[ $key ] ){
					foreach ( $note_keys as $note_key ) {
						if( $newKeys[ $note_key ] != $oldKeys[ $note_key ] ){
							$close = $newKeys[ $key ];
							$params = [ 'type' => $key, 'note' => $newKeys[ $note_key ], 'properties' => $newKeys ];
							Cockpit_Community_Status_Log::registerNote( $params );
						}
					}
				}
			}
		}
		parent::save();
	}

	public function type() {
		if (!isset($this->_type)) {
			$rs = $this->restaurants();
			$third = false;
			$first = false;
			$takeout = false;

			foreach ($rs as $r) {
				if ($r->delivery_service && $r->delivery) {
					$third = true;
					continue;
				}
				if (!$r->delivery_service) {
					$first = true;
					continue;
				}
				if ($r->takeout) {
					$takeout = true;
					continue;
				}
			}

			if (!$third && !$first && $takeout) {
				$this->_type = 'takeout';
			} elseif($third && $first) {
				$this->_type = 'both';
			} elseif ($third) {
				$this->_type = '3rd';
			} elseif ($first) {
				$this->_type = '1st';
			} else {
				$this->_type = 'none';
			}
		}
		return $this->_type;
	}

	/**
	 * Returns all the restaurants that belong to this Community
	 *
	 * @return Cana_Iterator
	 *
	 * @todo probably not required to sort them as the front end sorts them
	 */
	public function restaurants() {
		if (!isset($this->_restaurants)) {
			$this->_restaurants = Restaurant::q('
				SELECT
					restaurant.*
					, restaurant_community.sort
				FROM restaurant
					left join restaurant_community using(id_restaurant)
				WHERE
					id_community=?
					and restaurant.active=true
				ORDER by
					restaurant_community.sort,
					restaurant.delivery DESC, restaurant.name ASC
			',[$this->id_community]);
/*
			$this->_restaurants->sort([
				'function' => 'open'
			]);
*/
		}
		return $this->_restaurants;
	}

	/**
	 * Returns all data related to this Community
	 *
	 * @return array
	 *
	 * @see Cana_Table::exports()
	 */
	public function exports() {

		$out = $this->properties();
		$out[ 'name_alt' ] = $this->name_alt();
		$out[ 'prep' ] = $this->prep();
		$out['type'] = $this->type();

		if( $out[ 'close_all_restaurants_id_admin' ] ){
			$admin = Admin::o( $out[ 'close_all_restaurants_id_admin' ] );
			$out[ 'close_all_restaurants_admin' ] = $admin->name;
			$date = new DateTime( $out[ 'close_all_restaurants_date' ], new DateTimeZone( c::config()->timezone ) );
			$out[ 'close_all_restaurants_date' ] = $date->format( 'M jS Y g:i:s A T' );
		}

		if( $out[ 'close_3rd_party_delivery_restaurants_id_admin' ] ){
			$admin = Admin::o( $out[ 'close_3rd_party_delivery_restaurants_id_admin' ] );
			$out[ 'close_3rd_party_delivery_restaurants_admin' ] = $admin->name;
			$date = new DateTime( $out[ 'close_3rd_party_delivery_restaurants_date' ], new DateTimeZone( c::config()->timezone ) );
			$out[ 'close_3rd_party_delivery_restaurants_date' ] = $date->format( 'M jS Y g:i:s A T' );
		}

		$next_sort = Crunchbutton_Community_Alias::q( 'SELECT MAX(sort) AS sort FROM community_alias WHERE id_community = ' . $this->id_community );
		if( $next_sort->sort ){
			$sort = $next_sort->sort + 1;
		} else {
			$sort = 1;
		}
		$out['next_sort'] = $sort;

		if( $out[ 'dont_warn_till' ] ){
			$out[ 'dont_warn_till' ] = [ 	'y' => $this->dontWarnTill()->format( 'Y' ), 'm' => $this->dontWarnTill()->format( 'm' ), 'd' => $this->dontWarnTill()->format( 'd' ), 'h' => $this->dontWarnTill()->format( 'H' ), 'i' => $this->dontWarnTill()->format( 'i' ) ];
			$out[ 'dont_warn_till_formated' ] = $this->dontWarnTill()->format( 'M jS Y g:i:s A T' );
			$out[ 'dont_warn_till_enabled' ] = true;
		} else {
			$out[ 'dont_warn_till' ] = null;
		}

		if( $out[ 'reopen_at' ] ){
			$_reopen_at = $this->reopenAt();
			$out[ 'reopen_at_utc' ] = [ 	'y' => $_reopen_at->format( 'Y' ), 'm' => $_reopen_at->format( 'm' ), 'd' => $_reopen_at->format( 'd' ), 'h' => $_reopen_at->format( 'H' ), 'i' => $_reopen_at->format( 'i' ) ];
			$out[ 'reopen_at_utc_formated' ] = $_reopen_at->format( 'M jS Y g:i:s A T' );


			$_reopen_at = $this->reopenAt( true );
			$out[ 'reopen_at' ] = [ 	'y' => $_reopen_at->format( 'Y' ), 'm' => $_reopen_at->format( 'm' ), 'd' => $_reopen_at->format( 'd' ), 'h' => $_reopen_at->format( 'H' ), 'i' => $_reopen_at->format( 'i' ) ];
			$out[ 'reopen_at_formated' ] = $_reopen_at->format( 'M jS Y g:i:s A T' );
			$out[ 'reopen_at_enabled' ] = true;

		} else {
			$out[ 'reopen_at_utc' ] = null;
			$out[ 'reopen_at' ] = null;
		}

		$out[ 'driver_group' ] = $this->driver_group()->name;
		$out[ 'amount_per_order' ] = floatval( $this->amount_per_order );

		foreach ($this->restaurants() as $restaurant) {
			$out['_restaurants'][$restaurant->id_restaurant.' '] = $restaurant->exports(['categories' => true, 'eta' => true]);
		}

		$note = $this->lastNote();
		if( $note ){
			$out[ 'note' ] = $note->exports();
		}

		return $out;
	}

	public function configExports(){
		$out = $this->properties();
		$unset = [
			'id_community',
			'loc_lat',
			'loc_lon',
			'active',
			'automatic_driver_restaurant_name',
			'private',
			'prep',
			'name_alt',
			'driver_group',
			'close_all_restaurants',
			'close_all_restaurants_note',
			'close_3rd_party_delivery_restaurants',
			'close_3rd_party_delivery_restaurants_note',
			'close_3rd_party_delivery_restaurants_note',
			'close_all_restaurants_id_admin',
			'close_3rd_party_delivery_restaurants_id_admin',
			'close_all_restaurants_date',
			'close_3rd_party_delivery_restaurants_date',
			'id_driver_restaurant',
			'driver_restaurant_name',
			'auto_close',
			'dont_warn_till',
			'reopen_at',
			'is_auto_closed',
			'delivery_logistics',
			'id_driver_group',
			'closed_message',
			'driver_checkin',
			'combine_restaurant_driver_hours',
			'top',
			'drivers_can_open',
			'drivers_can_close',
			'notify_customer_when_driver_open',
			'auto_close_predefined_message',
			'amount_per_order',
			'campus_cash',
			'campus_cash_name',
			'campus_cash_validation',
			'campus_cash_fee',
			'campus_cash_mask',
			'campus_cash_receipt_info',
			'campus_cash_default_payment',
			'signature',
			'last_down_to_help_out_message',
			'campus_cash_delivery_confirmation',
			'id',
			'range',
			'timezone',
			'stored' ];
		foreach( $unset as $rem ){
			unset( $out[ $rem ] );
		}
		foreach( $out as $key => $val ){
			if( is_null( $val ) ){
				unset( $out[ $key ] );
			}
			if( is_numeric( $val ) ){
				$out[ $key ] = floatval( $val );
			}
			if( $val === false ){
				unset( $out[ $key ] );
			}
		}

		return $out;
	}

	public function lastNote(){
		return Cockpit_Community_Note::lastNoteByCommunity( $this->id_community );
	}

	public function allRestaurantsClosed(){
		return $this->close_all_restaurants;
	}

	public function allThirdPartyDeliveryRestaurantsClosed(){
		if( $this->close_3rd_party_delivery_restaurants ){
			return $this->close_3rd_party_delivery_restaurants;
		}
		return $this->isAutoClosed();
	}

	public function dontWarnTill(){
		if( $this->dont_warn_till && !$this->_dont_warn_till ){
			$this->_dont_warn_till = new DateTime( $this->dont_warn_till, new DateTimeZone( Community_Shift::CB_TIMEZONE ) );
		}
		return $this->_dont_warn_till;
	}

	public function reopenAt( $community_tz = false ){
		if( $this->reopen_at ){
			$date = new DateTime( $this->reopen_at, new DateTimeZone( c::config()->timezone ) );
			if( $community_tz ){
				$date->setTimezone( new DateTimeZone( $this->timezone ) );
			}
			return $date;
		}
		return null;
	}

	public static function permalink($permalink) {
		return self::q('select * from community where permalink=?', [$permalink])->get(0);
	}

	public static function all_locations(){
		$res = Cana::db()->query( 'SELECT c.id_community, c.loc_lat, c.loc_lon, c.range FROM community c' );
		$locations = array();
		while ( $row = $res->fetch() ) {
			$locations[ $row->id_community ] = array( 'loc_lat' => floatval( $row->loc_lat ), 'loc_lon' => floatval( $row->loc_lon ), 'range' => floatval( $row->range ) );
		}
		return $locations;
	}

	public function name_alt(){
		$alias = Community_Alias::alias( $this->permalink );
		if( !$alias ){
			$alias = Community_Alias::community( $this->id_community );
		}
		if( $alias ){
			return $alias[ 'name_alt' ];
		}
		return $this->name_alt;
	}

	public function aliases(){
		if( !$this->_aliases ){
			$this->_aliases = Crunchbutton_Community_Alias::q( 'SELECT * FROM community_alias WHERE id_community = ? ORDER BY alias ASC', [$this->id_community]);
		}
		return $this->_aliases;
	}

	public function prep(){
		$alias = Community_Alias::alias( $this->permalink );
		if( !$alias ){
			$alias = Community_Alias::community( $this->id_community );
		}
		if( $alias ){
			return $alias[ 'prep' ];
		}
		return $this->prep;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('community')
			->idVar('id_community')
			->load($id);
	}

	function driver_group(){
		if( !$this->_driver_group ){
			$this->_driver_group = Crunchbutton_Group::o( $this->id_driver_group );
		}
		return $this->_driver_group;
	}

	function createGroups(){
		$this->groupOfDrivers();
		$this->groupOfMarketingReps();
	}

	// this method will create (if necessary) and return a the driver's group
	function groupOfDrivers(){
		if (!isset($this->_groupOfDrivers)) {
			$group = $this->driver_group();
			if (!$group->id_group) {
				$group_name = Crunchbutton_Group::driverGroupOfCommunity( $this->name );
				$group = Crunchbutton_Group::createDriverGroup( $group_name, $this->name, $this->id_community);
				$this->id_driver_group = $group->id_group;
				$this->driver_group = $group_name;
				$this->save();
			}
			$this->_groupOfDrivers = $group;
		}
		return $this->_groupOfDrivers;
	}

	function groupOfMarketingReps(){
		if (!isset($this->_groupOfMarketingReps)) {
			$group = Crunchbutton_Group::q( 'SELECT * FROM `group` WHERE id_community = ? AND type = ? ORDER BY id_group DESC LIMIT 1 ', [$this->id_community, Crunchbutton_Group::TYPE_MARKETING_REP]);
			if (!$group->id_group) {
				$group = Crunchbutton_Group::createMarketingRepGroup( $this->id_community );
			}
			$this->_groupOfMarketingReps = $group;
		}
		return $this->_groupOfMarketingReps;
	}

	public function communityByDriverGroup( $group ){
		die('deprecated #5359');
		return Crunchbutton_Community::q( 'SELECT * FROM community WHERE driver_group = ?', [$group]);
	}

	public function active(){
		return Crunchbutton_Community::q( 'SELECT * FROM community WHERE active = true ORDER BY name ASC' );
	}

	public function getDriversOfCommunity(){
		$group = $this->driver_group()->id_group;
		$query = 'SELECT a.* from admin a INNER JOIN admin_group ag ON ag.id_admin = a.id_admin AND ag.id_group = ? WHERE a.active = 1 ORDER BY a.name ASC';
		return Admin::q( $query, [$group]);
	}

	public function workingCommunityCS(){
		if(!$this->_workingCommunityCS){
			$community_cs = $this->communityCS();
			$this->_workingCommunityCS = [];
			foreach($community_cs as $cs){
				if($cs->isWorking()){
					$this->_workingCommunityCS[] = $cs;
				}
			}
			// When only one driver is on shift at a community, send them all texts from customers in that community #8437
			if(!count($this->_workingCommunityCS)){
				foreach($community_cs as $cs){
					if($cs->hasWorkedForTheLastHours(3)){
						$this->_workingCommunityCS[] = $cs;
					}
				}
			}
		}
		return $this->_workingCommunityCS;
	}

	public function hasWorkingCommunityCS(){
		return count($this->workingCommunityCS()) > 0;
	}

	public function hasCommunityCS(){
		return $this->communityCS()->count() > 0;
	}

	public function communityCS(){
		if(!$this->_communityCS){
			$group = $this->driver_group()->id_group;
			$query = 'SELECT a.* from admin a
								INNER JOIN admin_group ag ON ag.id_admin = a.id_admin AND ag.id_group = ?
								INNER JOIN (SELECT ag.id_admin FROM admin_group ag INNER JOIN `group` g ON ag.id_group = g.id_group AND g.name = ?) community_cs ON community_cs.id_admin = a.id_admin
								WHERE a.active = 1 ORDER BY a.name ASC';
			$this->_communityCS = Admin::q( $query, [$group, Crunchbutton_Group::TYPE_COMMUNITY_CS]);
		}
		return $this->_communityCS;
	}

	public function slug(){
		return str_replace( ' ' , '-', strtolower( $this->name ) );
	}

	public function totalUsersByCommunity(){
		$chart = new Crunchbutton_Chart_User();
		$total = $chart->totalUsersByCommunity( $this->id_community );
		$all = $chart->totalUsersAll();

		$percent = intval( $total * 100 / $all );

		return [ 'community' => $total, 'all' => $all, 'percent' => $percent ];
	}

	public function totalOrdersByCommunity(){
		$chart = new Crunchbutton_Chart_Order();
		$total = $chart->totalOrdersByCommunity( $this->id_community );
		$all = $chart->totalOrdersAll();

		$percent = intval( $total * 100 / $all );

		return [ 'community' => $total, 'all' => $all, 'percent' => $percent ];
	}

	public function newUsersLastWeek(){

		$chart = new Crunchbutton_Chart_User();

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$now->modify( '-1 day' );
		$chart->dayTo = $now->format( 'Y-m-d' );
		$now->modify( '-6 days' );
		$chart->dayFrom = $now->format( 'Y-m-d' );
		$chart->justGetTheData = true;
		$orders = $chart->newByDayByCommunity( false, $this->slug() );

		$now->modify( '+6 day' );

		$_orders = [];

		// fill empty spaces
		for( $i = 0; $i <= 6 ; $i++ ){
			$_orders[ $now->format( 'Y-m-d' ) ] = ( $orders[ $now->format( 'Y-m-d' ) ] ? $orders[ $now->format( 'Y-m-d' ) ] : '0' );
			$now->modify( '-1 day' );
		}

		$total = 0;
		$week = [];

		foreach( $_orders as $day => $value ){
			$total += $value;
			$week[] = $value;
		}
		return [ 'total' => $total, 'week' => join( ',', $week ) ];
	}

	public function getOrdersFromLastDaysByCommunity( $days = 14 ){

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$now->modify( '- ' . $days . ' days' );
		$days_ago = $now->format( 'Y-m-d' );

		$query = "SELECT SUM(1) orders, DATE_FORMAT( o.date, '%m/%d/%Y' ) day FROM `order` o
					INNER JOIN restaurant r ON r.id_restaurant = o.id_restaurant
					INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant AND rc.id_community = ?
					WHERE o.date > '$days_ago' AND o.name NOT LIKE '%test%' GROUP BY day ORDER BY o.date ASC";
		return c::db()->get( $query, [$this->id_community]);
	}

	public function ordersLastWeek(){

		$chart = new Crunchbutton_Chart_Order();

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$now->modify( '-1 day' );
		$chart->dayTo = $now->format( 'Y-m-d' );
		$now->modify( '-6 days' );
		$chart->dayFrom = $now->format( 'Y-m-d' );
		$chart->justGetTheData = true;
		$orders = $chart->byDayPerCommunity( false, $this->slug() );

		$now->modify( '+6 day' );

		$_orders = [];

		// fill empty spaces
		for( $i = 0; $i <= 6 ; $i++ ){
			$_orders[ $now->format( 'Y-m-d' ) ] = ( $orders[ $now->format( 'Y-m-d' ) ] ? $orders[ $now->format( 'Y-m-d' ) ] : '0' );
			$now->modify( '-1 day' );
		}

		$total = 0;
		$week = [];

		foreach( $_orders as $day => $value ){
			$total += $value;
			$week[] = $value;
		}
		return [ 'total' => $total, 'week' => join( ',', $week ) ];
	}

	public function getRestaurants(){
		return Restaurant::q( 'SELECT * FROM restaurant r INNER JOIN restaurant_community rc ON rc.id_restaurant = r.id_restaurant AND rc.id_community = ? ORDER BY r.name', [$this->id_community]);
	}

	public function driverDeliveryHere( $id_admin ){
		$group = $this->groupOfDrivers();

		if( $group->id_group ){
			$admin_group = Crunchbutton_Admin_Group::q( "SELECT * FROM admin_group ag WHERE ag.id_group = ? AND ag.id_admin = ? LIMIT 1", [$group->id_group, $id_admin]);
			if( $admin_group->id_admin_group ){
				return true;
			}
			return false;
		} else {
			return false;
		}
		return false;
	}

	// legacy - returns the driver's group name
	public function driverGroup(){
		$group = $this->driver_group();
		if( $group->name ){
			return $group->name;
		}
	}

	public function marketingRepGroup(){
		if( !$this->_marketing_rep_group ){
			$this->_marketing_rep_group = Crunchbutton_Group::marketingRepGroupOfCommunity( $this->id_community );
		}
		return $this->_marketing_rep_group;
	}

	/**
	 * Returns the Testing community
	 *
	 * @return Crunchbutton_Community
	 */
	public function getTest(){
		$row = $this->q('SELECT * FROM community WHERE name="Testing" ')->current();
		return $row;
	}

	public function totalDriversByCommunity(){

		$drivers = $this->getDriversOfCommunity();
		$total = $drivers->count();

		$drivers = Admin::drivers();
		$all = $drivers->count();

		$percent = intval( $total * 100 / $all );

		return [ 'community' => $total, 'all' => $all, 'percent' => $percent ];
	}

	public function hasShiftThisWeek(){
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone  ) );
		if( $now->format( 'l' ) == 'Sunday' ){
			$day = $now;
		} else {
			$day = new DateTime( 'last sunday', new DateTimeZone( c::config()->timezone  ) );
		}
		$from = $day->format( 'Y-m-d' );
		$day->modify( '+6 days' );
		$to = $day->format( 'Y-m-d' );
		$shifts = Crunchbutton_Community_Shift::q('
			SELECT COUNT(*) AS shifts FROM community_shift cs
			WHERE
				cs.date_start >= ?
				AND cs.date_end <= ?
				AND id_community = ?
			ORDER BY cs.date_start ASC
		', [$from, $to, $this->id_community]);
		return ( $shifts->shifts > 0 );
	}

	public function hasShiftByPeriod( $from = false, $to = false ){
		return Crunchbutton_Community_Shift::shiftsByCommunityPeriod( $this->id_community, $from, $to );
	}

	public function totalRestaurantsByCommunity(){

		$query = "SELECT COUNT(*) AS Total FROM restaurant r INNER JOIN restaurant_community rc ON rc.id_restaurant = r.id_restaurant AND rc.id_community = ?";

		$result = c::db()->get( $query, [$this->id_community]);
		$total = $result->_items[0]->Total;

		$query = "SELECT COUNT(*) AS Total FROM restaurant WHERE active = true AND name NOT LIKE '%test%'";
		$result = c::db()->get( $query );
		$all = $result->_items[0]->Total;

		$percent = intval( $total * 100 / $all );

		return [ 'community' => $total, 'all' => $all, 'percent' => $percent ];
	}

	public function closedSince( $sortDate = false ){
		$force_closed_times = Crunchbutton_Community_Changeset::q('
			SELECT ccs.*, cc.field FROM community_change cc
			INNER JOIN community_change_set ccs ON ccs.id_community_change_set = cc.id_community_change_set AND id_community = ?
			AND ( cc.field = ? OR cc.field = ? )
			AND cc.new_value = \'1\'
			ORDER BY cc.id_community_change DESC
		', [$this->id_community, 'close_all_restaurants', 'close_3rd_party_delivery_restaurants']);
		$out = [];
		if( $force_closed_times->count() ){
			foreach( $force_closed_times as $force_close ){
				$output = [];
				$closed_at = $force_close->date();
				if( $sortDate ){
					$output[ 'sort_date' ] = $closed_at->format( 'YmdHis' );
				}
				$output[ 'closed_at' ] = $closed_at->format( 'M jS Y g:i:s A T' );
				$closed_by = $force_close->admin()->name;
				if( !$closed_by ){
					// it probably was closed by auto shutdown
					$closed_by = Admin::login( Crunchbutton_Community::AUTO_SHUTDOWN_COMMUNITY_LOGIN )->name;
				}
				$output[ 'closed_by' ] = $closed_by;
				if( $force_close->field == 'close_all_restaurants' ){
					$output[ 'type' ] = 'Close All Restaurants';
				} else if ( $force_close->field == 'close_3rd_party_delivery_restaurants' ){
					$output[ 'type' ] = 'Close 3rd Party Delivery Restaurants';
				}
				$output[ 'note' ] = $this->closedNote( $force_close->id_community_change_set, $force_close->field );
				$open = $this->_openedAt( $closed_at->format( 'Y-m-d H:i:s' ), $force_close->field );
				if( !$open ){
					$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
					$interval = $now->diff( $closed_at );
					$output[ 'how_long' ] = Crunchbutton_Util::format_interval( $interval );
					$out[] = $output;
				}
			}
		}
		// if the community was closed before we start logging it
		else {
			if( $this->close_all_restaurants ){
				$output = [];
				$closed_at = new DateTime( $this->close_all_restaurants_date, new DateTimeZone( c::config()->timezone ) );
				$output[ 'type' ] = 'Close All Restaurants';
				$output[ 'closed_at' ] = $closed_at->format( 'M jS Y g:i:s A T' );
				if( $sortDate ){
					$output[ 'sort_date' ] = $closed_at->format( 'YmdHis' );
				}
				$output[ 'closed_by' ] = Admin::o( $this->close_all_restaurants_id_admin )->name;
				$output[ 'note' ] = $this->close_all_restaurants_note;
				$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
				$interval = $now->diff( $closed_at );
				$output[ 'how_long' ] = Crunchbutton_Util::format_interval( $interval );
				$out[] = $output;
			}
			if( $this->close_3rd_party_delivery_restaurants ){
				$output = [];
				if( $this->close_3rd_party_delivery_restaurants_date ){
					$closed_at = new DateTime( $this->close_3rd_party_delivery_restaurants_date, new DateTimeZone( c::config()->timezone ) );
					$output[ 'closed_at' ] = $closed_at->format( 'M jS Y g:i:s A T' );
					if( $sortDate ){
						$output[ 'sort_date' ] = $closed_at->format( 'YmdHis' );
					}
					$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
					$interval = $now->diff( $closed_at );
					$output[ 'how_long' ] = Crunchbutton_Util::format_interval( $interval );
				} else {
					$output[ 'closed_at' ] = '-';
				}
				$output[ 'type' ] = 'Close 3rd Party Delivery Restaurants';
				$output[ 'closed_by' ] = Admin::o( $this->close_3rd_party_delivery_restaurants_id_admin )->name;
				$output[ 'note' ] = $this->close_3rd_party_delivery_restaurants_note;
				$out[] = $output;
			}
		}

		return $out;
	}

	public function forceCloseLog( $days = 30 ){
		$limit_date = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$limit_date->modify( '- ' . $days . ' days' );
		return Cockpit_Community_Status_Log::q( 'SELECT * FROM community_status_log WHERE id_community = ? AND closed_date >= ? ORDER BY id_community_status_log DESC', [ $this->id_community, $limit_date->format( 'Y-m-d' ) ] );
	}

	public static function closedNote( $id_community_change_set, $field ){
		$field = ( $field == 'is_auto_closed' ? 'close_3rd_party_delivery_restaurants' : $field );
		$field = $field . '_note';
		$note = Crunchbutton_Community_Changeset::q('
			SELECT
			ccs.*, cc.field, cc.new_value FROM community_change cc
			INNER JOIN community_change_set ccs ON ccs.id_community_change_set = cc.id_community_change_set
			AND cc.field = ?
			AND ccs.id_community_change_set = ?
			ORDER BY cc.id_community_change DESC LIMIT 1
		',[$field, $id_community_change_set])->get(0);
		if( $note->new_value ){
			return $note->new_value;
		}
		return false;
	}

	public function shutDownCommunities( $dt = null ){
		$communities = Crunchbutton_Community::q( 'SELECT * FROM community WHERE automatic_driver_restaurant_name = 1' );
		foreach( $communities as $community ){
			$community->changeDriverRestaurantName();
		}
		$communities = Crunchbutton_Community::q( 'SELECT * FROM community WHERE auto_close = 1' );
		foreach( $communities as $community ){
			$community->shutDownCommunity( $dt );
		}
		// Remove force close from communities
		Community::removeForceClose();

		// Call the method that reopen auto closed communities with drivers
		Crunchbutton_Community::reopenAutoClosedCommunities();
	}

	public function reopenAutoClosedCommunities(){

		$admin = Admin::login( Crunchbutton_Community::AUTO_SHUTDOWN_COMMUNITY_LOGIN );
		$id_admin = $admin->id_admin;
		$communities = Crunchbutton_Community::q( 'SELECT * FROM community WHERE close_all_restaurants_id_admin = "' . $id_admin . '" OR close_3rd_party_delivery_restaurants_id_admin = "' . $id_admin . '" OR is_auto_closed = true' );
		foreach( $communities as $community ){
			$community->reopenAutoClosedCommunity();
		}
		$communities = Crunchbutton_Community::q( 'SELECT * FROM community WHERE close_all_restaurants = 1 OR close_3rd_party_delivery_restaurants = 1' );
		foreach( $communities as $community ){
			$community->checkIfClosedCommunityHasDrivers();
		}
	}

	public static function removeForceClose(){

		$communities = Community::q( 'SELECT * FROM community WHERE close_all_restaurants = 1 OR close_3rd_party_delivery_restaurants = 1 AND reopen_at IS NOT NULL' );
		if( $communities->count() ){
			$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
			foreach( $communities as $community ){
				$reopen_at = $community->reopenAt();
				if( $reopen_at && intval( $now->format( 'YmdHis' ) ) >= intval( $reopen_at->format( 'YmdHis' ) ) ){
					$community->close_all_restaurants = 0;
					$community->close_3rd_party_delivery_restaurants = 0;
					$community->reopen_at = null;
					$community->save();

					$ticket = 'The "force close" of the community ' . $community->name . ' was removed. ';
					echo $ticket;
					echo "\n\n";
					Crunchbutton_Support::createNewWarning(  [ 'body' => $ticket, 'bubble' => true ] );
				}
			}
		}
	}

	public function checkIfClosedCommunityHasDrivers(){

		if( $this->id_community && ( $this->allThirdPartyDeliveryRestaurantsClosed() || $this->allRestaurantsClosed() ) ){

			$admin = Admin::login( Crunchbutton_Community::AUTO_SHUTDOWN_COMMUNITY_LOGIN );
			$id_admin = $admin->id_admin;

			if( $this->close_all_restaurants_id_admin != $id_admin && $this->close_3rd_party_delivery_restaurants_id_admin != $id_admin ){

				$nextShifts = Crunchbutton_Community_Shift::currentAssignedShiftByCommunity( $this->id_community );

				if( $nextShifts && $nextShifts->count() ){

					foreach( $nextShifts as $nextShift ){

						if( $nextShift->id_community_shift ){

							$createTicket = true;
							$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );

							$dont_warn_till = $this->dontWarnTill();
							if( $dont_warn_till ){
								$now->setTimezone( new DateTimeZone( Community_Shift::CB_TIMEZONE ) );
								if( $dont_warn_till && intval( $dont_warn_till->format( 'YmdHis' ) ) > intval( $now->format( 'YmdHis' ) ) ){
									$createTicket = false;
								}
								$now->setTimezone( new DateTimeZone( c::config()->timezone ) );
							}

							$date_start = $nextShift->dateStart( $this->timezone );
							$date_start->setTimezone( new DateTimeZone( c::config()->timezone ) );
							$date_end = $nextShift->dateEnd( $this->timezone );
							$date_end->setTimezone( new DateTimeZone( c::config()->timezone ) );

							if( $createTicket && $now->format( 'YmdHis' ) >= $date_start->format( 'YmdHis' ) && $now->format( 'YmdHis' ) <= $date_end->format( 'YmdHis' ) ){
								$ticket = 'Hey! You should probably reopen ' . $this->name . ', which is currently closed, because there\'s a driver scheduled for right now!! But please double check to make sure this wasn\'t done on purpose. If it was done on purpose because the community is overwhelmed, then hustle to get us an additional driver! Do whatever it takes!';
								echo $ticket;
								echo "\n\n";
								Log::debug( [ 'id_community' => $this->id_community, 'nextShift' => $nextShift->id_community_shift, 'message' => $ticket, 'type' => 'community-auto-reopened' ] );
								Crunchbutton_Support::createNewWarning(  [ 'body' => $ticket, 'bubble' => true ] );
							}
							return;
						}
					}
				}
			}
		}
	}

	public function reopenAutoClosedCommunity(){

		if( !$this->auto_close ){ return; }

		if( $this->id_community && ( $this->allThirdPartyDeliveryRestaurantsClosed() || $this->allRestaurantsClosed() || $this->is_auto_closed ) ){

			$admin = Admin::login( Crunchbutton_Community::AUTO_SHUTDOWN_COMMUNITY_LOGIN );
			$id_admin = $admin->id_admin;

			if( $this->close_3rd_party_delivery_restaurants_id_admin == $id_admin || $this->isAutoClosed() ){

				$nextShifts = Crunchbutton_Community_Shift::currentAssignedShiftByCommunity( $this->id_community );

				if( $nextShifts && $nextShifts->count() ){

					 foreach ( $nextShifts as $nextShift ) {

							if( $nextShift->id_community_shift && ( $this->driver_checkin && $nextShift->isConfirmed() || !$this->driver_checkin ) ){

									$date_start = $nextShift->dateStart( $this->timezone );
									$date_start->setTimezone( new DateTimeZone( c::config()->timezone ) );
									$date_end = $nextShift->dateEnd( $this->timezone );
									$date_end->setTimezone( new DateTimeZone( c::config()->timezone ) );

									$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );

									if( $now->format( 'YmdHis' ) >= $date_start->format( 'YmdHis' )  && $now->format( 'YmdHis' ) <= $date_end->format( 'YmdHis' ) ){

										$ticket = 'The community ' . $this->name . ' was auto reopened.';

										echo $ticket . "\n";

										// Open the community
										$this->is_auto_closed = 0;
										$this->save();

										Log::debug( [ 'id_community' => $this->id_community, 'nextShift' => $nextShift->id_community_shift, 'message' => $ticket, 'type' => 'community-auto-reopened' ] );
										Crunchbutton_Support::createNewWarning(  [ 'body' => $ticket ] );
									}
								}
							}
					 } else if($this->auto_close) {

					 	$nextShift = Crunchbutton_Community_Shift::nextAssignedShiftByCommunity( $this->id_community );
						if( $nextShift->id_community ){

							$date_start = $nextShift->dateStart( $this->timezone );
							$date_end = $nextShift->dateEnd( $this->timezone );

							$message = 'Next open ';
							$message .= $date_start->format( 'g' );
							if( $date_start->format( 'i' ) != '00' ){
								$message .= ':' . $date_start->format( 'i' );
							}
							$message .= $date_start->format( 'A' );
							$message .= '-';

							$day = clone $nextShift->dateStart( $this->timezone );

							$shift_day = $day->format( 'Y-m-d' );
							$day->modify( '+1 day' );
							$shift_next_day = $day->format( 'Y-m-d' );

							// last Shift
							$lastShift = Crunchbutton_Community_Shift::q( "SELECT * FROM community_shift cs WHERE cs.date_start >= ? AND cs.date_end <= ? AND cs.id_community = ? ORDER BY cs.date_end DESC LIMIT 1", [ $shift_day, $shift_next_day, $this->id_community ] )->get( 0 );
							if( $lastShift->id_community_shift ){
								$date_end = $lastShift->dateEnd( $this->timezone );
							}
							$message .= $date_end->format( 'g' );
							if( $date_end->format( 'i' ) != '00' ){
								$message .= ':' . $date_end->format( 'i' );
							}

							$message .= $date_end->format( 'A' );
							$message .= ' ';
							$message .= $date_start->format( 'D' );
							$message .= '!';
						} else {
							$message = 'Temporarily Unavailable!';
						}
						$this->driver_restaurant_name = $message;
						$this->save();
					 }
				}
		}
	}

	public function activeDrivers( $dt = null ){
		$totalDrivers = 0;
		$drivers = $this->getDriversOfCommunity();
		$hasDriverWorking = false;
		// check if the drivers should checkin the shift, if it does, return just the driver that had checked in
		$checkedIn_confirm = intval( $this->driver_checkin ) > 0 ? true : false;
		foreach( $drivers as $driver ){
			if( $driver->isWorking( $dt, $this->id_community, $checkedIn_confirm ) ){
				$totalDrivers++;
			}
		}
		return $totalDrivers;
	}

	public function isAutoClosed(){
		return $this->is_auto_closed ? true : false;
	}

	// See #6908
	public function changeDriverRestaurantName(){

		if( !$this->automatic_driver_restaurant_name ){ return; }

		if( !$this->id_community ||
				$this->allThirdPartyDeliveryRestaurantsClosed() ||
				$this->isAutoClosed() ||
				$this->allRestaurantsClosed() ){
			return;
		}

		if( $this->activeDrivers( $dt ) > 0 ){
			$shift = Crunchbutton_Community_Shift::currentAssignedShiftByCommunity( $this->id_community );
			$shift = $shift->get( 0 );
		} else {
			$shift = Crunchbutton_Community_Shift::nextAssignedShiftByCommunity( $this->id_community );
		}

		if( $shift->id_community ){
			$date_start = $shift->dateStart( $this->timezone );
			$date_end = $shift->dateEnd( $this->timezone );

			$message = 'open ';
			$message .= $date_start->format( 'g' );
			if( $date_start->format( 'i' ) != '00' ){
				$message .= ':' . $date_start->format( 'i' );
			}
			$message .= $date_start->format( 'A' );
			$message .= '-';
			$message .= $date_end->format( 'g' );
			if( $date_end->format( 'i' ) != '00' ){
				$message .= ':' . $date_end->format( 'i' );
			}
			$message .= $date_start->format( 'A D' );
			$this->driver_restaurant_name = strtolower( $message );
			echo $this->driver_restaurant_name;
			echo "\n";
			$this->save();
		}
	}

	public function shutDownCommunity( $dt = null ){

		if( !$this->auto_close ){ return; }

		if( !$this->id_community ||
				$this->allThirdPartyDeliveryRestaurantsClosed() ||
				$this->allRestaurantsClosed() ){
			return;
		}

		// check if the community has any restaurant open
		$restaurants = $this->restaurants();
		$has3rdPartyDeliveryRestaurantsOpen = false;
		foreach( $restaurants as $restaurant ){
			if( $restaurant->open( $dt ) ){
				if($restaurant->delivery_service){
					$has3rdPartyDeliveryRestaurantsOpen = true;
				}
			}
		}

		if( $has3rdPartyDeliveryRestaurantsOpen ){
			if( $this->activeDrivers( $dt ) > 0 ){
				$hasDriverWorking = true;
			} else {
				$hasDriverWorking = false;
			}

			$close3rdParyDeliveryRestaurants = ( $has3rdPartyDeliveryRestaurantsOpen && !$hasDriverWorking );

			if( $close3rdParyDeliveryRestaurants ){

				$admin = Admin::login( Crunchbutton_Community::AUTO_SHUTDOWN_COMMUNITY_LOGIN );
				$id_admin = $admin->id_admin;

				$hasDriverThatDidntCheckin = false;
				$drivers = $this->getDriversOfCommunity();
				$hasDriverWorking = false;
				foreach( $drivers as $driver ){
					if( $driver->isWorking( $dt, $this->id_community, false ) ){
						$hasDriverThatDidntCheckin = true;
					}
				}

				if( $this->auto_close_predefined_message ){
					$message = $this->auto_close_predefined_message;
				} else if( $hasDriverThatDidntCheckin ){
					$message = 'Back Soon!';
				} else {
					$nextShift = Crunchbutton_Community_Shift::nextAssignedShiftByCommunity( $this->id_community );

					if( $nextShift->id_community ){

						$date_start = $nextShift->dateStart( $this->timezone );
						$date_end = $nextShift->dateEnd( $this->timezone );

						$message = 'Next open ';
						$message .= $date_start->format( 'g' );
						if( $date_start->format( 'i' ) != '00' ){
							$message .= ':' . $date_start->format( 'i' );
						}
						$message .= $date_start->format( 'A' );
						$message .= '-';

						$day = clone $nextShift->dateStart( $this->timezone );

						$shift_day = $day->format( 'Y-m-d' );
						$day->modify( '+1 day' );
						$shift_next_day = $day->format( 'Y-m-d' );

						// last Shift
						$lastShift = Crunchbutton_Community_Shift::q( "SELECT * FROM community_shift cs WHERE cs.date_start >= ? AND cs.date_end <= ? AND cs.id_community = ? ORDER BY cs.date_end DESC LIMIT 1", [ $shift_day, $shift_next_day, $this->id_community ] )->get( 0 );
						if( $lastShift->id_community_shift ){
							$date_end = $lastShift->dateEnd( $this->timezone );
						}
						$message .= $date_end->format( 'g' );
						if( $date_end->format( 'i' ) != '00' ){
							$message .= ':' . $date_end->format( 'i' );
						}

						$message .= $date_end->format( 'A' );
						$message .= ' ';
						$message .= $date_start->format( 'D' );
						$message .= '!';
					} else {
						$message = 'Temporarily Unavailable!';
					}
				}

				echo $message;
				echo "\n";

				// Close the community
				$this->is_auto_closed = 1;
				$this->driver_restaurant_name = $message;
				// remove notes from #6788
				$this->close_all_restaurants_note = '';
				$this->close_3rd_party_delivery_restaurants_note = '';

				$ticket = '';

				if( $hasDriverThatDidntCheckin ){
					$ticket .= 'The community ' . $this->name . ' was auto closed due to it has no checked in drivers.' . "\n";
				} else {
					$ticket .= 'The community ' . $this->name . ' was auto closed due to it has no drivers.' . "\n";
				}

				$ticket .= 'The community message was set to: "' . $message . '"' . "\n";

				if( $nextShift->id_community ){
					$ticket .= 'that is when the next shift will start.';
				} else {
					$ticket .= 'Because it has no next shift with drivers.';
				}

				$reason = new Cockpit_Community_Closed_Reason;
				$reason->id_admin = Admin::login( Crunchbutton_Community::AUTO_SHUTDOWN_COMMUNITY_LOGIN )->id_admin;
				$reason->id_community = $this->id_community;
				$reason->reason = $ticket;
				$reason->type = Cockpit_Community_Closed_Reason::TYPE_AUTO_CLOSED;
				$reason->date = date( 'Y-m-d H:i:s' );
				$reason->save();

				$this->id_community_closed_reason = $reason->id_community_closed_reason;

				$this->save();

				echo $ticket;
				echo "\n";

				Log::debug( [ 'id_community' => $this->id_community, 'nextShift' => $nextShift->id_community_shift, 'message' => $ticket, 'type' => 'community-auto-closed' ] );
				// Crunchbutton_Support::createNewWarning(  [ 'body' => $ticket ] );
			}
		}
	}

	public function driverRestaurant(){
		if( $this->id_driver_restaurant ){
			return Restaurant::o( $this->id_driver_restaurant );
		}
		return false;
	}

	public function closedMessage(){
		return $this->closed_message;
	}

	public function saveClosedMessage(){
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$now->modify( '- 1 day' );
		$from = $now->format( 'Y-m-d' ) . ' 00:00:00';
		$now->modify( '+ 6 days' );
		$to = $now->format( 'Y-m-d' ) . ' 23:59:59';

		$shifts = Community_Shift::q( 'SELECT cs.* FROM community_shift cs INNER JOIN admin_shift_assign asa ON cs.id_community_shift = asa.id_community_shift WHERE cs.date_start >= ? AND cs.date_end <= ? AND id_community = ? ORDER BY cs.date_start ASC', [ $from, $to, $this->id_community ] );

		$hours = [];

		foreach( $shifts as $shift ){

			$start = $shift->dateStart();
			$end = $shift->dateEnd();

			$hours[ strtolower( $start->format( 'D' ) ) ] = [];

			$hours[ strtolower( $start->format( 'D' ) ) ] = [ 'from' => $start->format( 'H:i' ), 'to' => null, 'status' => 'open' ];

			if( $start->format( 'Ymd' ) < $end->format( 'Ymd' ) ){
				$hours[ strtolower( $start->format( 'D' ) ) ][ 'to' ] = '00:00';
				$hours[ strtolower( $end->format( 'D' ) ) ] = [ 'from' => $end->format( 'H:i' ), 'to' => null, 'status' => 'open' ];
			}
			$hours[ strtolower( $end->format( 'D' ) ) ][ 'to' ] = $end->format( 'H:i' );
		}

		uksort( $hours,
			function( $a, $b ) {
				$weekdays = [ 'mon' => 0, 'tue' => 1, 'wed' => 2, 'thu' => 3, 'fri' => 4, 'sat' => 5, 'sun' => 6 ];
				return( $weekdays[ $a ] > $weekdays[ $b ] );
			} );

		$weekdays = [ 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun' ];
		foreach( $weekdays as $day ){
			if( $_hours[ $day ] ){
				$index = array_search( $day, $weekdays );
				// Get the prev day to compare
				if( $index == 0 ){
					$index_prev = count( $weekdays ) - 1;
				} else if( $index == ( count( $weekdays ) - 1 ) ){
					$index_prev = 0;
				} else {
					$index_prev--;
				}
				$prev_day = $weekdays[ $index_prev ];
				// the current day
				if( $_hours[ $day ] ){
					// If this days starts at midnight that is a chance this hours belongs to prev day
					if( $_hours[ $day ] && $_hours[ $day ][ 0 ] && $_hours[ $day ][ 0 ][ 'from' ] && $_hours[ $day ][ 0 ][ 'from' ] == '0:00' ){
						if( $_hours[ $prev_day ] && $_hours[ $prev_day ][ count( $_hours[ $prev_day ] ) - 1 ] && $_hours[ $prev_day ][ count( $_hours[ $prev_day ] ) - 1 ][ 'from' ] && $_hours[ $prev_day ][ count( $_hours[ $prev_day ] ) - 1 ][ 'to' ] == '0:00' ){
							$_hours[ $prev_day ][ count( $_hours[ $prev_day ] ) - 1 ] = array( 'from' => $_hours[ $prev_day ][ count( $_hours[ $prev_day ] ) - 1 ][ 'from' ], 'to' => $_hours[ $day ][ 0 ][ 'to' ] );
							unset( $_hours[ $day ][ 0 ] );
							$_hours[ $day ] = array_values( $_hours[ $day ] );
						}
					}
				}
			}
		}

		// Convert the hours to format am/pm and merge the segments
		$_partial = [];
		$_hours = $hours;
		foreach ( $_hours as $day => $hours ) {
			$segments = [];
			$segments[] = $hours[ 'from' ] . ' - ' . $hours[ 'to' ];
			$_partial[ $day ] = join( ', ', $segments );
		}
		$this->closed_message = str_replace( '<br/>', "\n", Hour::closedMessage( $_partial ) );
		$this->save();
	}

	public function _openedAt( $date, $field ){
		$query = '
			SELECT
			ccs.*, cc.field FROM community_change cc
			INNER JOIN community_change_set ccs ON ccs.id_community_change_set = cc.id_community_change_set AND id_community = ?
			AND cc.field = ?
			AND ( cc.new_value = \'0\' OR cc.new_value IS NULL ) AND ccs.timestamp > ?
			ORDER BY cc.id_community_change ASC LIMIT 1
		';
		$opened = Crunchbutton_Community_Changeset::q($query, [$this->id_community, $field, $date])->get(0);
		if( $opened->id_community_change_set ){
			return $opened;
		}
		return false;
	}

	public function currentShift(){
		return Crunchbutton_Community_Shift::currentShiftByCommunity( $this->id_community )->get( 0 );
	}

	public function assignedShiftHours( $allDay = false ){
		if( !$this->_assigned_shift_hours ){
			$this->_assigned_shift_hours = Crunchbutton_Community_Shift::assignedShiftHours( $this->id_community, $allDay );
		}
		return $this->_assigned_shift_hours;
	}

	public function shiftsForNextWeek( $todayAssigned = false ){
		if( !$this->_next_week_shifts ){
			$this->_next_week_shifts = Crunchbutton_Community_Shift::shiftsForNextWeek( $this->id_community, $todayAssigned );
		}
		return $this->_next_week_shifts;
	}

	public function assignedShiftsForNextWeek(){
			if( !$this->_assigned_shift_for_next_week ){
			$this->_assigned_shift_for_next_week = Crunchbutton_Community_Shift::shiftsForNextWeek( $this->id_community, false, true );
		}
		return $this->_assigned_shift_for_next_week;
	}

	// should return a smart value based on what time it is. for now just return db value
	public function campusTime() {
		return 1;
	}

	public function driverRestaurantName(){
		if( $this->close_all_restaurants && trim( $this->close_all_restaurants_note ) != '' ){
			return $this->close_all_restaurants_note;
		}
		if( $this->close_3rd_party_delivery_restaurants  && trim( $this->close_3rd_party_delivery_restaurants_note ) != '' ){
			return $this->close_3rd_party_delivery_restaurants_note;
		}
		if( $this->is_auto_closed && $this->driver_restaurant_name ){
			return $this->driver_restaurant_name;
		}
		return 'Temporarily Unavailable';
	}

	public function operationHours(){

		$shiftsForNextWeek = $this->shiftsForNextWeek();

		$hours = [];
		$weekdays = [ 'mon' => 0, 'tue' => 1, 'wed' => 2, 'thu' => 3, 'fri' => 4, 'sat' => 5, 'sun' => 6 ];
		foreach( $shiftsForNextWeek as $hour ){
			$open = explode( ':', $hour->time_open );
			$close = explode( ':', $hour->time_close );
			$day = $hour->day;

			$open_day = $weekdays[ $day ];
			$close_day = $weekdays[ $day ];

			if( intval( $open[ 0 ] ) > intval( $close[ 0 ] ) ){
				$close_day += 1;
			}

			$hour->open = ( $open_day * 2400 ) + intval( $open[ 0 ] * 100 ) + $open[ 1 ];
			$hour->close = ( $close_day * 2400 ) + intval( $close[ 0 ] * 100 ) + $close[ 1 ];
			$hours[] = (array) $hour;

		}

		$hours = Cana_Util::sort_col( $hours, 'open' );

		$hours = self::mergeHours( $hours );

		$_hours = [];
		foreach( $hours as $key => $hour ){
			$day = $hour[ 'day' ];
			if( !$_hours[ $day ] ){
				$_hours[ $day ] = [];
			}
			$_hours[ $day ][] = [ 'from' => $hour[ 'time_open' ], 'to' => $hour[ 'time_close' ], 'status' => 'open' ];
		}
		$operation_hours = Hour::restaurantClosedMessage( null, $_hours );
		return str_replace( ' <br/>', ', ', $operation_hours );
	}

	public static function mergeHours( $hours ){
		$reprocess = false;
		foreach( $hours as $key => $val ){
			$getNext = false;
			foreach( $hours as $keyNext => $valNext ){
				if( $getNext ){
					if( $hours[ $keyNext ][ 'open' ] <= $hours[ $key ][ 'close' ]
							&& $hours[ $keyNext ][ 'close' ] - $hours[ $key ][ 'open' ] < 3600 ) {
						$hours[ $key ][ 'close' ] = $hours[ $keyNext ][ 'close' ];
						$hours[ $key ][ 'time_close' ] = $hours[ $keyNext ][ 'time_close' ];
						unset( $hours[ $keyNext ] );
						$reprocess = true;
						$getNext = false;
					}
				}
				if( $key == $keyNext ){
					$getNext = true;
				}
			}
		}
		if( $reprocess ){
			return self::mergeHours( $hours );
		}
		return $hours;
	}

  public function communityspeed($time, $dow) {
      $qString = "SELECT * FROM `order_logistics_communityspeed` WHERE id_community= ? and "
          ."time_start_community <= ? and time_end_community > ? and day_of_week = ?";
      $cs = Crunchbutton_Order_Logistics_Communityspeed::q($qString, [$this->id_community, $time, $time, $dow]);
      if (is_null($cs) || $cs->count()==0){
          return null;
      } else{
          return $cs->get(0);
      }
  }

	public function fakecustomers() {
		$qString = "SELECT * FROM `order_logistics_fakecustomer` WHERE id_community= ?";
		$fcs = Crunchbutton_Order_Logistics_Fakecustomer::q($qString, [$this->id_community]);
		if (is_null($fcs) || $fcs->count()==0){
			return null;
		} else{
			return $fcs;
		}
	}

	public function logisticsBundleParams($bundleSize) {
		$qString = "SELECT * FROM `order_logistics_bundleparam` WHERE id_community= ? and "
			."bundle_size = ?";
		$bp = Crunchbutton_Order_Logistics_Bundleparam::q($qString, [$this->id_community, $bundleSize]);
		if (is_null($bp) || $bp->count()==0){
			return null;
		} else{
			return $bp->get(0);
		}
	}

	public function logisticsParams($algoVersion) {
		$qString = "SELECT * FROM `order_logistics_param` WHERE id_community= ? and algo_version = ?";
		$p = Crunchbutton_Order_Logistics_Param::q($qString, [$this->id_community, $algoVersion]);
		if (is_null($p) || $p->count()==0){
			return null;
		} else{
			return $p->get(0);
		}
	}

	public function deliveryHours( $currentDay = false ){
		$hours = [];
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$from = $now->format( 'Y-m-d' );
		$now->modify( '+ 6 days' );
		$to = $now->format( 'Y-m-d' );
		$shifts = Crunchbutton_Community_Shift::assignedShiftsByCommunityPeriod( $this->id_community, $from, $to );
		foreach( $shifts as $shift ){
			$start = $shift->dateStart()->format( 'H:i' );
			$end = $shift->dateEnd()->format( 'H:i' );
			$weekday = strtolower( $shift->dateEnd()->format( 'D' ) );
			if( !$hours[ $weekday ] ){
				$hours[ $weekday ] = [];
			}
			$hours[ $weekday ] = [ $start, $end ];
		}
		uksort( $hours,
		function( $a, $b ) {
			$weekdays = [ 'mon' => 0, 'tue' => 1, 'wed' => 2, 'thu' => 3, 'fri' => 4, 'sat' => 5, 'sun' => 6 ];
			return( $weekdays[ $a ] > $weekdays[ $b ] );
		} );
		if( $currentDay ){
			return $hours[ strtolower( $now->format( 'D' ) ) ];
		}
		return $hours;
	}

    public function communityCenter() {
        if (is_null($this->loc_lat) || is_null($this->loc_lon)) {
            return null;
        }
        else{
            return new Crunchbutton_Order_Location($this->loc_lat, $this->loc_lon);
        }
    }

	public function doCreateFakeOrders() {
		//TODO: Do not necessarily want to create fake orders all the time for bundling
		return true;
	}

	public function addNote( $text ){
		$note = new Cockpit_Community_Note;
		$note->id_community = $this->id_community;
		$note->id_admin = c::user()->id_admin;
		$note->date = date( 'Y-m-d H:i:s' );
		$note->text = $text;
		$note->save();
		return $note;
	}

	public function isElegibleToBeOpened(){
		if( $this->id_community && $this->drivers_can_open ){
			$shift = Crunchbutton_Community_Shift::currentAssignedShiftByCommunity( $this->id_community );
			if( $shift->id_community_shift ){
				return false;
			}
			if( $this->allThirdPartyDeliveryRestaurantsClosed() || $this->allRestaurantsClosed() || $this->is_auto_closed || !$shift ){
				return true;
			}
		}
		return false;
	}

	public function isElegibleToBeClosed(){
		if( $this->id_community && $this->drivers_can_close ){
			if( $this->allThirdPartyDeliveryRestaurantsClosed() || $this->allRestaurantsClosed() || $this->is_auto_closed || !$this->isOpen()){
				return false;
			}
			return true;
		}
		return false;
	}


	public function isOpen(){
		$shift = Crunchbutton_Community_Shift::currentAssignedShiftByCommunity( $this->id_community );
		return ( !$this->allThirdPartyDeliveryRestaurantsClosed() && !$this->allRestaurantsClosed() && !$this->is_auto_closed && $shift->id_community_shift );
	}

	public static function createShiftForNonScheduledDriver( $id_community ){

		$community = Community::o( $id_community );

		if( !$community->id_community ){
			return false;
		}

		$driver = c::admin();

		$shouldCreateAShift = !$driver->isWorking();

		if( $shouldCreateAShift ){
			// don't create 1 hour shifts for drivers whose shifts just ended #7395
			$lastShift = Community_Shift::lastShiftsByAdmin( $driver->id_admin, 1 );
			if( $lastShift->count() ){
				$lastShift = $lastShift->get( 0 );
				$ended = $lastShift->dateEnd();
				$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
				$now->setTimezone( new DateTimeZone( $lastShift->community()->timezone ) );
				$minutes = Crunchbutton_Community_Shift::driverBufferBeforeCreateShift();
				$ended->modify( '+ ' . $minutes . ' minutes' );
				if( $ended->format( 'YmdHis' ) > $now->format( 'YmdHis' ) ){
					$shouldCreateAShift = false;
				}
			}
		}

		if( $shouldCreateAShift ){

			$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
			$now->setTimezone( new DateTimeZone( $community->timezone ) );
			$start = $now->format( 'Y-m-d H:i' );
			$now->modify( '+ 1 hour' );

			$end = null;

			$shift = Crunchbutton_Community_Shift::nextShiftByAdmin( $driver->id_admin, 1 );
			if ($shift) {
				$shift = $shift->get( 0 );

				if( $shift->id_community_shift ){
					$shiftStart = $shift->dateStart();

					if( $now > $shiftStart ){
						$end = $shiftStart->format( 'Y-m-d H:i' );
						// checking driver
						$assignment = Crunchbutton_Admin_Shift_Assign::o( $shift->id_admin_shift_assign );
						if( $assignment->id_community_shift ){
							$assignment->confirmed = true;
							$assignment->save();
						}
					}
				}
			}

			if( !$end ){
				$end = $now->format( 'Y-m-d H:i' );
			}

			$newShift = new Crunchbutton_Community_Shift();
			$newShift->id_community = $community->id_community;
			$newShift->date_start = $start;
			$newShift->date_end = $end;
			$newShift->active = 1;
			$newShift->created_by_driver = 1;
			$newShift->date = date('Y-m-d H:i:s');;
			$newShift->id_driver = $driver->id_admin;
			if( $newShift->date_start && $newShift->date_end ){
				$newShift->save();
				$newShift = Crunchbutton_Community_Shift::o( $newShift->id_community_shift );
			}

			if( $newShift->id_community_shift ){
				$assignment = new Crunchbutton_Admin_Shift_Assign();
				$assignment->id_admin = $driver->id_admin;
				$assignment->id_community_shift = $newShift->id_community_shift;
				$assignment->confirmed = true;
				$assignment->date = date('Y-m-d H:i:s');
				$assignment->save();
			}
			return $newShift;
		}
	}

	public function closeCommunityByDriver($id_driver, $minutes, $reason){
		// check if the driver belongs to the community
		$driver = Admin::o( $id_driver );

		$communities = $driver->driverCommunities();
		$canClose = false;
		foreach( $communities as $community ){
			if( $community->id_community == $this->id_community ){
				$canClose = true;
			}
		}

		if(!$canClose){
			return self::DRIVER_OPEN_COMMUNITY_ERROR_COMMUNITY;
		}

		$reopen_at = new DateTime( 'now', new DateTimeZone(c::config()->timezone));
		$reopen_at->modify('+ ' . $minutes . ' minutes');
		$reopen_at = $reopen_at->format('Y-m-d H:i:s');

		$dont_warn_till = new DateTime( 'now', new DateTimeZone(Crunchbutton_Community_Shift::CB_TIMEZONE));
		$dont_warn_till->modify('+ ' . $minutes . ' minutes');
		$dont_warn_till = $dont_warn_till->format('Y-m-d H:i:s');

		$this->close_3rd_party_delivery_restaurants = true;
		$this->close_3rd_party_delivery_restaurants_id_admin = $driver->id_admin;
		$this->close_3rd_party_delivery_restaurants_id_admin = $driver->id_admin;
		$this->reopen_at = $reopen_at;
		$this->dont_warn_till = $dont_warn_till;

		$closedReason = new Cockpit_Community_Closed_Reason;
		$closedReason->id_admin = c::user()->id_admin;
		$closedReason->id_community = $this->id_community;
		$closedReason->type = Cockpit_Community_Closed_Reason::TYPE_3RD_PARTY_DELIVERY_RESTAURANTS;
		$closedReason->date = date( 'Y-m-d H:i:s' );
		$closedReason->reason = $reason;
		$closedReason->save();

		$this->save();

		$message = 'The community ' . $this->name . ' was closed';
		$message .= ' by ' . $driver->name;
		$message .= ' during the period of ' . $minutes . ' minutes.';
		$message .= ' Reason: ' . $reason;
		Crunchbutton_Support::createNewWarning( [ 'staff' => true, 'phone' => $driver->phone, 'bubble' => true, 'body' => $message ] );
		return true;
	}

	public function openCommunityByDriver( $id_driver, $shiftEnd ){
		// check if the driver belongs to the community
		$driver = Admin::o( $id_driver );

		$communities = $driver->driverCommunities();
		$open = false;
		foreach( $communities as $community ){
			if( $community->id_community == $this->id_community ){
				$open = true;
			}
		}

		if( $open ){

			$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
			$now->setTimezone( new DateTimeZone( $this->timezone ) );

			$end = str_replace( ':', '', $shiftEnd );

			if( $now->format( 'Hi' ) > $end ){
				return self::DRIVER_OPEN_COMMUNITY_ERROR_SHIFT_HOURS;
			}

			$start = $now->format( 'Y-m-d H:i' );
			$end = $now->format( 'Y-m-d ' ) . $shiftEnd;

			$newShift = new Crunchbutton_Community_Shift();
			$newShift->id_community = $this->id_community;
			$newShift->date_start = $start;
			$newShift->date_end = $end;
			$newShift->active = 1;
			$newShift->created_by_driver = 1;
			$newShift->hidden = 1;
			$newShift->date = date('Y-m-d H:i:s');;
			$newShift->id_driver = $driver->id_admin;
			if( $newShift->date_start && $newShift->date_end ){
				$newShift->save();
				$newShift = Crunchbutton_Community_Shift::o( $newShift->id_community_shift );
			}

			if( $newShift->id_community_shift ){
				$assignment = new Crunchbutton_Admin_Shift_Assign();
				$assignment->id_admin = $driver->id_admin;
				$assignment->id_community_shift = $newShift->id_community_shift;
				$assignment->confirmed = true;
				$assignment->date = date('Y-m-d H:i:s');
				$assignment->save();

				$pexcard = $driver->pexcard();
				if($pexcard){
					$pexcard->addShiftStartFunds( $shift->id_admin_shift_assign );
				}
				if( $assignment->id_admin_shift_assign ){

					$message = 'The community ' . $this->name . ' was ';
					$status = [];
					if( $this->is_auto_closed ){
						$this->is_auto_closed = false;
						$status[] = 'auto-closed ';
					}

					if( $this->close_3rd_party_delivery_restaurants ){
						$this->close_3rd_party_delivery_restaurants = false;
						$status[] = '3rd party delivere restaurants closed ';
					}
					if( $this->close_all_restaurants ){
						$this->close_all_restaurants = false;
						$status[] = 'all restaurants closed ';
					}
					$this->save();
					$message .= join( ',', $status );
					$message .= 'but it was reopened by ' . $driver->name;
					$message .= ' during the period ' . $newShift->startEndToString();
					Crunchbutton_Support::createNewWarning( [ 'staff' => true, 'phone' => $driver->phone, 'bubble' => true, 'body' => $message ] );
					$this->createNotificationForCommunityOpenedByDriver();
					return true;
				} else {
					return self::DRIVER_OPEN_COMMUNITY_ERROR_ASSIGNING_SHIFT;
				}
			} else {
				return self::DRIVER_OPEN_COMMUNITY_ERROR_CREATING_SHIFT;
			}
		}
		return self::DRIVER_OPEN_COMMUNITY_ERROR_COMMUNITY;
	}

	public function campusCash(){
		return ( $this->campus_cash ) ? true : false;
	}

	public function campusCashName(){
		if( $this->campusCash() && $this->campus_cash_name ){
			return $this->campus_cash_name;
		}
		return null;
	}

	public function campusCashDefaultPaymentMethod(){
		if( $this->campusCash() && $this->campus_cash_default_payment ){
			return $this->campus_cash_default_payment;
		}
		return false;
	}

	public function campusCashReceiptInfo(){
		if( $this->campusCash() && $this->campus_cash_receipt_info ){
			return $this->campus_cash_receipt_info;
		}
		return false;
	}


	public function campusCashDeliveryLocatedOnCampus(){
		if( $this->campusCash() && $this->campus_cash_delivery_confirmation ){
			return true;
		}
		return false;
	}



	public function campusCashFee(){
		if( $this->campusCash() && $this->campus_cash_fee ){
			return floatval( max( $this->campus_cash_fee, 0 ) );
		}
		return 0;
	}

	public function campusCashMask(){
		if( $this->campusCash() && $this->campus_cash_mask ){
			return $this->campus_cash_mask;
		}
		return null;
	}

	public function campusCashValidate( $card ){
		if( $this->campusCash() ){
			if( $this->campus_cash_validation ){
				preg_match( $this->campus_cash_validation, $card, $results );
				if( $results[ 0 ] ){
					return trim( $results[ 0 ] );
				}
				return false;
			}
		}
		return false;
	}

	public function requireSignature(){
		if( $this->campusCash() && $this->signature ){
			return true;
		}
		return false;
	}


	public function hasPreOrders(){
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$now->modify('-1 day');
		$yesterday = $now->format('Y-m-d');
		$query = "SELECT
							COUNT(*) AS total
							FROM `order` o
							LEFT JOIN order_action oa ON oa.id_order_action = o.delivery_status
							WHERE o.id_community = ? AND o.preordered = 1
							AND o.preordered_date >= ?
							AND ( ( oa.type != ? AND oa.type != ? ) OR oa.type IS NULL )";
		$total = c::db()->get( $query, [ $this->id_community, $yesterday, Crunchbutton_Order_Action::DELIVERY_DELIVERED, Crunchbutton_Order_Action::DELIVERY_CANCELED ] )->get( 0 );
		return intval( $total->total );
	}

	public function preorderMinAfterCommunityOpen(){
		if( $this->preorder_min_after_community_open ){
			return intval( $this->preorder_min_after_community_open );
		} else {
			return self::PREORDER_MINUTES_AFTER_COMMUNITY_OPEN_DEFAULT;
		}
	}

	public static function customerCommunityByPhone($phone){
		// returns the community that the phone belongs to
		$query = 'SELECT id_community FROM `order` o INNER JOIN phone p ON p.id_phone = o.id_phone AND p.phone = ? ORDER BY o.id_order DESC LIMIT 1';
		return self::q($query, [$phone]);
	}

	public function createNotificationForCommunityOpenedByDriver(){
		if (Community_Notification::notifyCommunityWhenIsOpenedByDriver() && $this->notify_customer_when_driver_open) {
			$config = Community_Notification::openByDriverNotifyConfig();
			$notifications = [];
			if ($config[Community_Notification::CONFIG_KEY_OPEN_BY_DRIVER_NOTIFY_EMAIL]) {
				$notifications[] = Community_Notification::NOTIFICATION_TYPE_EMAIL;
			}
			if ($config[Community_Notification::CONFIG_KEY_OPEN_BY_DRIVER_NOTIFY_PUSH]) {
				$notifications[] = Community_Notification::NOTIFICATION_TYPE_PUSH;
			}
			if ($config[Community_Notification::CONFIG_KEY_OPEN_BY_DRIVER_NOTIFY_SMS]) {
				$notifications[] = Community_Notification::NOTIFICATION_TYPE_SMS;
			}
			$days = $config[Community_Notification::CONFIG_KEY_OPEN_BY_DRIVER_NOTIFY_DAYS];
			$message = $config[Community_Notification::CONFIG_KEY_OPEN_BY_DRIVER_NOTIFY_MSG];
			foreach ($notifications as $notification) {
				Community_Notification::create([	'id_community' => $this->id_community,
																					'notification_type' => $notification,
																					'message' => $message,
																					'customer_period' => $days ]);
			}
		}
	}

	public function sendMessageToDriversFillTheirPreferences(){
		return ($this->message_drivers_fill_preferences ? true : false);
	}

	public function remindDriversaboutTheirShifts(){
		return ($this->remind_drivers_about_their_shifts ? true : false);
	}

	// Smart population of "our most popular locations" on UI2 #6056
	public static function smartSortPopulation(){
		$query = Crunchbutton_Custom_Query::mostPopularLocationQuery();
		if( $query ){
			$results = $query->run();
			if( $results ){
				c::dbWrite()->query( 'UPDATE community SET top = 0' );
				$position = 1;
				foreach( $results as $result ){
					$community = Crunchbutton_Community::o( $result->id_community );
					if( $community->id_community ){
						$community->top = $position;
						$community->save();
						$position++;
					}
				}
			}
		}
	}
}
