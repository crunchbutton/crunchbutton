<?php

class Crunchbutton_Community extends Cana_Table {
	public static function all($force = null) {
		$ip = preg_replace('/[^0-9\.]+/','',$_SERVER['REMOTE_ADDR']);
		$force = preg_replace('/[^a-z\-]+/','',$force);
		if ($force) {
			$forceq = ' OR (community.permalink="'.c::db()->escape($force).'") ';
		}

		$q = '
			select community.* from community
			left join community_ip on community_ip.id_community=community.id_community
			where
				community.active=1
				AND (
					( community.private=0 )
					OR
					(community.private=1 AND community_ip.ip="'.c::db()->escape($ip).'")
					'.$forceq.'
				)
			group by community.id_community
			order by name
		';

		return self::q($q);
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
						id_community="'.$this->id_community.'"
					and restaurant.active=1
				ORDER by
					restaurant_community.sort,
					restaurant.delivery DESC
			');
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
		foreach ($this->restaurants() as $restaurant) {
			$out['_restaurants'][$restaurant->id_restaurant.' '] = $restaurant->exports();
		}
		return $out;
	}

	public function allRestaurantsClosed(){
		if( $this->close_all_restaurants > 0 ){
			return $this->close_all_restaurants_note;
		}
		return false;
	}

	public function allThirdPartyDeliveryRestaurantsClosed(){
		if( $this->close_3rd_party_delivery_restaurants > 0 ){
			return $this->close_3rd_party_delivery_restaurants_note;
		}
		return false;
	}


	public static function permalink($permalink) {
		return self::q('select * from community where permalink="'.$permalink.'"')->get(0);
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
		return false;
	}

	public function prep(){
		$alias = Community_Alias::alias( $this->permalink );
		if( !$alias ){
			$alias = Community_Alias::community( $this->id_community );
		}
		if( $alias ){
			return $alias[ 'prep' ];
		}
		return false;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('community')
			->idVar('id_community')
			->load($id);
	}


	function groupOfDrivers(){
		$group = Crunchbutton_Group::byName( $this->driverGroup() );
		if( $group->id_group ){
			return $group;
		}
		$group = Crunchbutton_Group::createDriverGroup( $this->driverGroup(), $this->name );
		return $group;
	}

	public function communityByDriverGroup( $group ){
		return Crunchbutton_Community::q( 'SELECT * FROM community WHERE driver_group = "' . $group . '"' );
	}

	public function active(){
		return Crunchbutton_Community::q( 'SELECT * FROM community WHERE active = 1 ORDER BY name ASC' );
	}

	public function getDriversOfCommunity(){
		$group = $this->driverGroup();
		$query = 'SELECT a.* FROM admin a
												INNER JOIN (
													SELECT DISTINCT(id_admin) FROM (
													SELECT DISTINCT(a.id_admin)
														FROM admin a
														INNER JOIN notification n ON n.id_admin = a.id_admin AND n.active = 1
														INNER JOIN admin_notification an ON a.id_admin = an.id_admin AND an.active = 1
														INNER JOIN restaurant r ON r.id_restaurant = n.id_restaurant
														INNER JOIN restaurant_community c ON c.id_restaurant = r.id_restaurant AND c.id_community = ' . $this->id_community . '
													UNION
													SELECT DISTINCT(a.id_admin) FROM admin a
														INNER JOIN admin_group ag ON ag.id_admin = a.id_admin
														INNER JOIN `group` g ON g.id_group = ag.id_group AND g.name = "' . $group . '"
														INNER JOIN admin_notification an ON a.id_admin = an.id_admin AND an.active = 1
														) drivers
													)
											drivers ON drivers.id_admin = a.id_admin AND a.active = 1 ORDER BY name ASC';
		return Admin::q( $query );
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
					INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant AND rc.id_community = '{$this->id_community}'
					WHERE o.date > DATE_SUB(CURDATE(), INTERVAL $days DAY) AND o.name NOT LIKE '%test%' GROUP BY day ORDER BY o.date ASC";
		return c::db()->get( $query );
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
		return Restaurant::q( 'SELECT * FROM restaurant r INNER JOIN restaurant_community rc ON rc.id_restaurant = r.id_restaurant AND rc.id_community = ' . $this->id_community . ' ORDER BY r.name' );
	}

	public function driverDeliveryHere( $id_admin ){
		$group = $this->groupOfDrivers();
		if( $group->id_group ){
			$admin_group = Crunchbutton_Admin_Group::q( "SELECT * FROM admin_group ag WHERE ag.id_group = {$group->id_group} AND ag.id_admin = {$id_admin} LIMIT 1" );
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
		$shifts = Crunchbutton_Community_Shift::q( 'SELECT COUNT(*) AS shifts FROM community_shift cs WHERE DATE_FORMAT( cs.date_start, "%Y-%m-%d" ) >= "' . $from . '" AND DATE_FORMAT( cs.date_end, "%Y-%m-%d" ) <= "' . $to . '" AND id_community = "' . $this->id_community . '" ORDER BY cs.date_start ASC' );
		return ( $shifts->shifts > 0 );
	}

	public function hasShiftByPeriod( $from = false, $to = false ){
		return Crunchbutton_Community_Shift::shiftsByCommunityPeriod( $this->id_community, $from, $to );
	}

	public function totalRestaurantsByCommunity(){

		$query = "SELECT COUNT(*) AS Total FROM restaurant r INNER JOIN restaurant_community rc ON rc.id_restaurant = r.id_restaurant AND rc.id_community = {$this->id_community}";

		$result = c::db()->get( $query );
		$total = $result->_items[0]->Total;

		$query = "SELECT COUNT(*) AS Total FROM restaurant WHERE active = 1 AND name NOT LIKE '%test%'";
		$result = c::db()->get( $query );
		$all = $result->_items[0]->Total;

		$percent = intval( $total * 100 / $all );

		return [ 'community' => $total, 'all' => $all, 'percent' => $percent ];
	}

}