<?php

class Crunchbutton_Community extends Cana_Table_Trackchange {

	const CUSTOMER_SERVICE_ID_COMMUNITY = 92;
	const CUSTOMER_SERVICE_COMMUNITY_GROUP = 'support';
	const AUTO_SHUTDOWN_COMMUNITY_LOGIN = 'autoshutdowncommunity';

	const TITLE_CLOSE_ALL_RESTAURANTS = 'Close All Restaurants';
	const TITLE_CLOSE_3RD_PARY_RESTAURANTS = 'Close 3rd Party Delivery Restaurants';
	const TITLE_CLOSE_AUTO_CLOSED = 'Auto Closed';

	public static function all($force = null) {
		$ip = preg_replace('/[^0-9\.]+/','',$_SERVER['REMOTE_ADDR']);
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
					restaurant.delivery DESC
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
		$out[ 'active' ] = intval( $out[ 'active' ] );
		$out[ 'private' ] = intval( $out[ 'private' ] );
		$out[ 'image' ] = intval( $out[ 'image' ] );
		$out[ 'close_all_restaurants' ] = intval( $out[ 'close_all_restaurants' ] );
		$out[ 'close_3rd_party_delivery_restaurants' ] = intval( $out[ 'close_3rd_party_delivery_restaurants' ] );
		$out[ 'auto_close' ] = intval( $out[ 'auto_close' ] );
		$out[ 'is_auto_closed' ] = intval( $out[ 'is_auto_closed' ] );
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
		} else {
			$out[ 'dont_warn_till' ] = null;
		}

		foreach ($this->restaurants() as $restaurant) {
			$out['_restaurants'][$restaurant->id_restaurant.' '] = $restaurant->exports(['categories' => true]);
		}

		return $out;
	}

	public function allRestaurantsClosed(){
		if( $this->close_all_restaurants > 0 ){
			return $this->close_all_restaurants;
		}
		return false;
	}

	public function allThirdPartyDeliveryRestaurantsClosed(){
		if( $this->close_3rd_party_delivery_restaurants > 0 ){
			return $this->close_3rd_party_delivery_restaurants;
		}
		return $this->isAutoClosed();
	}

	public function dontWarnTill(){
		if( $this->dont_warn_till ){
			return new DateTime( $this->dont_warn_till, new DateTimeZone( c::config()->timezone ) );
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
			$locations[ $row->id_community ] = array( 'loc_lat' => $row->loc_lat, 'loc_lon' => $row->loc_lon, 'range' => $row->range );
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

	function groupOfDrivers(){
		if (!isset($this->_groupOfDrivers)) {
			$group = Crunchbutton_Group::byName($this->driverGroup());
			if (!$group->id_group) {
				$group = Crunchbutton_Group::createDriverGroup($this->driverGroup(), $this->name);
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
		return Crunchbutton_Community::q( 'SELECT * FROM community WHERE driver_group = ?', [$group]);
	}

	public function active(){
		return Crunchbutton_Community::q( 'SELECT * FROM community WHERE active = true ORDER BY name ASC' );
	}

	public function getDriversOfCommunity(){
		$group = $this->driverGroup();

		$query = 'SELECT a.* FROM admin a
												INNER JOIN (
													SELECT DISTINCT(id_admin) FROM (
													SELECT DISTINCT(a.id_admin)
														FROM admin a
														INNER JOIN notification n ON n.id_admin = a.id_admin AND n.active = true
														LEFT JOIN admin_notification an ON a.id_admin = an.id_admin AND an.active = true
														INNER JOIN restaurant r ON r.id_restaurant = n.id_restaurant
														INNER JOIN restaurant_community c ON c.id_restaurant = r.id_restaurant AND c.id_community = ?
													UNION
													SELECT DISTINCT(a.id_admin) FROM admin a
														INNER JOIN admin_group ag ON ag.id_admin = a.id_admin
														INNER JOIN `group` g ON g.id_group = ag.id_group AND g.name = ?
														) drivers
													)
											drivers ON drivers.id_admin = a.id_admin AND a.active = true ORDER BY name ASC';
		return Admin::q( $query, [$this->id_community, $group]);
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
		$query = "SELECT SUM(1) orders, DATE_FORMAT( o.date, '%m/%d/%Y' ) day FROM `order` o
					INNER JOIN restaurant r ON r.id_restaurant = o.id_restaurant
					INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant AND rc.id_community = ?
					WHERE o.date > DATE_SUB(CURDATE(), INTERVAL $days DAY) AND o.name NOT LIKE '%test%' GROUP BY day ORDER BY o.date ASC";
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

	public function driverGroup(){
		if( !$this->driver_group ){
			$this->driver_group = Crunchbutton_Group::driverGroupOfCommunity( $this->name );
			$this->save();
		}
		return $this->driver_group;
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

	public function closedSince(){
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
				$output[ 'note' ] = $this->_closedNote( $force_close->id_community_change_set, $force_close->field );
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

	public function forceCloseLog( $echo = true, $remove_unclosed = false, $days = 30 ){

		$limit_date = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$limit_date->modify( '- ' . $days . ' days' );

		$force_closed_times = Crunchbutton_Community_Changeset::q('
			SELECT ccs.*, cc.field FROM community_change cc
			INNER JOIN community_change_set ccs ON ccs.id_community_change_set = cc.id_community_change_set AND id_community = ?
			AND ( cc.field = ? OR cc.field = ? OR cc.field = ? )
			AND cc.new_value = \'1\' AND date( timestamp ) > ?
			ORDER BY timestamp DESC
		', [$this->id_community, 'close_all_restaurants', 'close_3rd_party_delivery_restaurants', 'is_auto_closed', $limit_date->format( 'Y-m-d' )]);
		$out = [];
		$alreadyUsed_open = [];
		$alreadyUsed_closed = [];

		foreach( $force_closed_times as $force_close ){

			if( $alreadyUsed_closed[ $force_close->timestamp ] ){

			}
			$alreadyUsed_closed[ $force_close->timestamp ] = true;
			$output = [];
			$closed_at = $force_close->date();
			$output[ 'closed_at' ] = $closed_at->format( 'M jS Y g:i:s A T' );
			// $output[ 'closed_at_id_community_change' ] = $force_close->id_community_change_set;
			$closed_by = $force_close->admin()->name;
			if( !$closed_by ){
				// it probably was closed by auto shutdown
				$closed_by = Admin::login( Crunchbutton_Community::AUTO_SHUTDOWN_COMMUNITY_LOGIN )->name;
			}
			$output[ 'closed_by' ] = $closed_by;

			if( $force_close->field == 'close_all_restaurants' ){
				$output[ 'type' ] = Crunchbutton_Community::TITLE_CLOSE_ALL_RESTAURANTS;
			} else if ( $force_close->field == 'close_3rd_party_delivery_restaurants' ){
				$output[ 'type' ] = Crunchbutton_Community::TITLE_CLOSE_3RD_PARY_RESTAURANTS;
			} else if ( $force_close->field == 'is_auto_closed' ){
				$output[ 'type' ] = Crunchbutton_Community::TITLE_CLOSE_AUTO_CLOSED;
			}

			$output[ 'note' ] = $this->_closedNote( $force_close->id_community_change_set, $force_close->field );

			$open = $this->_openedAt( $closed_at->format( 'Y-m-d H:i:s' ), $force_close->field );

			if( $open && !$alreadyUsed_open[ $open->timestamp ] ){
				$alreadyUsed_open[ $open->timestamp ] = true ;
				$opened_at = $open->date();
				$output[ 'opened_at' ] = $opened_at->format( 'M jS Y g:i:s A T' );
				// $output[ 'opened_at_id_community_change' ] = $open->id_community_change_set;
				$opened_by = $open->admin()->name;
				if( !$opened_by ){
					// it probably was closed by auto shutdown
					$opened_by = Admin::login( Crunchbutton_Community::AUTO_SHUTDOWN_COMMUNITY_LOGIN )->name;
				}
				$output[ 'opened_by' ] = $opened_by;
				$interval = $opened_at->diff( $closed_at );
				$output[ 'how_long' ] = Crunchbutton_Util::format_interval( $interval );
			} else {

				if( $remove_unclosed ){
					$output = false;
				} else {
					if( !$this->$force_close->field ){
						$output[ 'how_long' ] = 'No records, probably variable was changed directly at database.';
					} else {
						$output[ 'how_long' ] = 'It is still closed!';
					}
				}
			}
			if( $output ){
				$out[] = $output;
			}
		}
		if( $echo ){
			echo json_encode( $out );exit;
		}
		return $out;
	}

	private function _closedNote( $id_community_change_set, $field ){
		$field = ( $field == 'is_auto_closed' ? 'close_3rd_party_delivery_restaurants' : $field );
		$field = $field . '_note';
		$note = Crunchbutton_Community_Changeset::q( 'SELECT
																											ccs.*, cc.field, cc.new_value FROM community_change cc
																											INNER JOIN community_change_set ccs ON ccs.id_community_change_set = cc.id_community_change_set AND id_community = "' . $this->id_community . '"
																											AND cc.field = "' . $field . '"
																											AND ccs.id_community_change_set = ' . $id_community_change_set . '
																											ORDER BY cc.id_community_change DESC LIMIT 1' )->get( 0 );
		if( $note->new_value ){
			return $note->new_value;
		}
		return false;
	}

	public function shutDownCommunities( $dt = null ){
		$communities = Crunchbutton_Community::q( 'SELECT * FROM community WHERE auto_close = 1' );
		foreach( $communities as $community ){
			$community->shutDownCommunity( $dt );
		}
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
		$communities = Crunchbutton_Community::q( 'SELECT * FROM community WHERE close_all_restaurants_id_admin != "' . $id_admin . '" OR close_3rd_party_delivery_restaurants_id_admin != "' . $id_admin . '"' );
		foreach( $communities as $community ){
			$community->checkIfClosedCommunityHasDrivers();
		}
	}

	public function checkIfClosedCommunityHasDrivers(){

		if( $this->id_community && ( $this->allThirdPartyDeliveryRestaurantsClosed() || $this->allRestaurantsClosed() ) ){

			$admin = Admin::login( Crunchbutton_Community::AUTO_SHUTDOWN_COMMUNITY_LOGIN );
			$id_admin = $admin->id_admin;

			if( $this->close_all_restaurants_id_admin != $id_admin && $this->close_3rd_party_delivery_restaurants_id_admin != $id_admin ){

				$nextShift =Crunchbutton_Community_Shift::currentAssignedShiftByCommunity( $this->id_community );
				if( $nextShift->id_community_shift ){
					$createTicket = true;
					$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
					$dont_warn_till = $this->dontWarnTill();
					if( $dont_warn_till && $dont_warn_till->format( 'YmdHis' ) >= $now->format( 'YmdHis' ) ){
						if( $createTicket ){
							$createTicket = false;
						}
					}
					$date_start = $nextShift->dateStart( $this->timezone );
					$date_start->setTimezone( new DateTimeZone( c::config()->timezone ) );
					$date_end = $nextShift->dateEnd( $this->timezone );
					$date_end->setTimezone( new DateTimeZone( c::config()->timezone ) );
					if( $createTicket && $now->format( 'YmdHis' ) >= $date_start->format( 'YmdHis' )  && $now->format( 'YmdHis' ) <= $date_end->format( 'YmdHis' ) ){
						$ticket = 'Hey! You should probably reopen ' . $this->name . ', which is currently closed, because there\'s a driver scheduled for right now!! But please double check to make sure this wasn\'t done on purpose. If it was done on purpose because the community is overwhelmed, then hustle to get us an additional driver! Do whatever it takes!';
						Log::debug( [ 'id_community' => $this->id_community, 'nextShift' => $nextShift->id_community_shift, 'message' => $ticket, 'type' => 'community-auto-reopened' ] );
						Crunchbutton_Support::createNewWarning(  [ 'body' => $ticket ] );
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

				$nextShift =Crunchbutton_Community_Shift::currentAssignedShiftByCommunity( $this->id_community );

				if( $nextShift->id_community_shift ){

					$date_start = $nextShift->dateStart( $this->timezone );
					$date_start->setTimezone( new DateTimeZone( c::config()->timezone ) );
					$date_end = $nextShift->dateEnd( $this->timezone );
					$date_end->setTimezone( new DateTimeZone( c::config()->timezone ) );

					$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );

					if( $now->format( 'YmdHis' ) >= $date_start->format( 'YmdHis' )  && $now->format( 'YmdHis' ) <= $date_end->format( 'YmdHis' ) ){

						// Open the community
						$this->is_auto_closed = 0;
						$this->save();

						$ticket = 'The community ' . $this->name . ' was auto reopened.';
						Log::debug( [ 'id_community' => $this->id_community, 'nextShift' => $nextShift->id_community_shift, 'message' => $ticket, 'type' => 'community-auto-reopened' ] );
						Crunchbutton_Support::createNewWarning(  [ 'body' => $ticket ] );
					}
				}
			}
		}
	}

	public function activeDrivers( $dt = null ){
		$totalDrivers = 0;
		$drivers = $this->getDriversOfCommunity();
		$hasDriverWorking = false;
		foreach( $drivers as $driver ){
			if( $driver->isWorking( $dt, $this->id_community ) ){
				$totalDrivers++;
			}
		}
		return $totalDrivers;
	}

	public function isAutoClosed(){
		return ( intval( $this->is_auto_closed ) > 0 );
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
				if( intval( $restaurant->delivery_service ) == 1 ){
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

				$nextShift = Crunchbutton_Community_Shift::nextAssignedShiftByCommunity( $this->id_community );

				if( $nextShift->id_community ){

					$date_start = $nextShift->dateStart( $this->timezone );
					$date_end = $nextShift->dateEnd( $this->timezone );

					$message = 'Next Delivering ';
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
					$message .= $date_end->format( 'A' );
					$message .= ' ';
					$message .= $date_start->format( 'l' );
					$message .= '!';
				} else {
					$message = 'Temporarily closed!';
				}

				echo $message;
				echo "\n";

				// Close the community
				$this->is_auto_closed = 1;
				$this->driver_restaurant_name = $message;
				$this->save();

				$ticket = 'The community ' . $this->name . ' was auto closed due to it has no drivers.' . "\n";
				$ticket .= 'The community message was set to: "' . $message . '"' . "\n";
				if( $nextShift->id_community ){
					$ticket .= 'that is when the next shift will start.';
				} else {
					$ticket .= 'Because it has no next shift with drivers.';
				}

				echo $ticket;
				echo "\n";

				Log::debug( [ 'id_community' => $this->id_community, 'nextShift' => $nextShift->id_community_shift, 'message' => $ticket, 'type' => 'community-auto-closed' ] );
				Crunchbutton_Support::createNewWarning(  [ 'body' => $ticket ] );
			}
		}
	}

	public function driverRestaurant(){
		if( $this->id_driver_restaurant ){
			return Restaurant::o( $this->id_driver_restaurant );
		}
		return false;
	}

	public function _openedAt( $date, $field ){
		$query = 'SELECT
								ccs.*, cc.field FROM community_change cc
								INNER JOIN community_change_set ccs ON ccs.id_community_change_set = cc.id_community_change_set AND id_community = "' . $this->id_community . '"
								AND cc.field = "' . $field . '"
								AND ( cc.new_value = 0 OR cc.new_value IS NULL ) AND ccs.timestamp > "' . $date . '"
								ORDER BY cc.id_community_change ASC LIMIT 1';
		$opened = Crunchbutton_Community_Changeset::q( $query )->get( 0 );
		if( $opened->id_community_change_set ){
			return $opened;
		}
		return false;
	}

	public function currentShift(){
		return Crunchbutton_Community_Shift::currentShiftByCommunity( $this->id_community )->get( 0 );
	}

	public function assignedShiftHours(){
		if( !$this->_assigned_shift_hours ){
			$this->_assigned_shift_hours = Crunchbutton_Community_Shift::assignedShiftHours( $this->id_community );
		}
		return $this->_assigned_shift_hours;
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
		return 'Temporarily closed';
	}
}