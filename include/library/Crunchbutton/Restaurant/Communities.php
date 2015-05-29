<?php

class Crunchbutton_Restaurant_Communities extends Cana_Table {
	
	public function __construct($id = null) {
		parent::__construct();
	}

	public function communityNameToSlug( $community ){
		return str_replace( ' ' , '-', strtolower( $community ) );
	}

	public function setSlug( $slug ){
		$this->slug = $slug;
		$name = $this->getCommunityNameBySlug( $slug );
		$this->setCommunity( $name );
	}

	public function name(){
		return $this->community;
	}

	public function setCommunity( $community ){
		$this->community = $community;
	}

	public function getCommunityNameBySlug( $slug ){
		$communities = Restaurant::getCommunities();
		foreach( $communities as $community ){
			if( $slug == Restaurant_Communities::communityNameToSlug( $community ) ){
				return $community;
			}
		}
		
	}

	public function restaurants(){
		return Restaurant::q( 'SELECT * FROM restaurant r WHERE r.community = "' . $this->community . '" ORDER BY r.active DESC, r.name ASC ' );
	}

	public function newUsersLastWeek(){
		if( !$this->community || !$this->slug ){
			return;
		}
		
		$chart = new Crunchbutton_Chart_User();

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$now->modify( '-1 day' );
		$chart->dayTo = $now->format( 'Y-m-d' );
		$now->modify( '-6 days' );
		$chart->dayFrom = $now->format( 'Y-m-d' );
		$chart->justGetTheData = true;
		$orders = $chart->newByDayByCommunity( false, $this->slug );

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

	public function totalDriversByCommunity(){

		if( !$this->community || !$this->slug ){
			return;
		}

		$drivers = $this->getDriversOfCommunity();
		$total = $drivers->count();

		$drivers = Admin::drivers();
		$all = $drivers->count();

		$percent = intval( $total * 100 / $all );

		return [ 'community' => $total, 'all' => $all, 'percent' => $percent ];		
	}

	public function getDriversOfCommunity(){

		$group = Crunchbutton_Group::driverGroupOfCommunity( $this->community );

		$query = 'SELECT a.* FROM admin a 
												INNER JOIN (
													SELECT DISTINCT(id_admin) FROM (
													SELECT DISTINCT(a.id_admin)
														FROM admin a
														INNER JOIN notification n ON n.id_admin = a.id_admin AND n.active = true
														INNER JOIN admin_notification an ON a.id_admin = an.id_admin AND an.active = true
														INNER JOIN restaurant r ON r.id_restaurant = n.id_restaurant AND r.community = ?
													UNION
													SELECT DISTINCT(a.id_admin) FROM admin a 
														INNER JOIN admin_group ag ON ag.id_admin = a.id_admin 
														INNER JOIN `group` g ON g.id_group = ag.id_group AND g.name = ?
														INNER JOIN admin_notification an ON a.id_admin = an.id_admin AND an.active = true
														) drivers
													) 
											drivers ON drivers.id_admin = a.id_admin ORDER BY name ASC';
		return Admin::q( $query, [$this->community, $group]);
	}


	public function totalRestaurantsByCommunity(){

		if( !$this->community || !$this->slug ){
			return;
		}

		$query = "SELECT COUNT(*) AS Total FROM restaurant WHERE community = ? AND name NOT LIKE '%test%'";

		$result = c::db()->get( $query, [$this->name()]);
		$total = $result->_items[0]->Total;

		$query = "SELECT COUNT(*) AS Total FROM restaurant WHERE active = true AND name NOT LIKE '%test%'";
		$result = c::db()->get( $query );
		$all = $result->_items[0]->Total; 	

		$percent = intval( $total * 100 / $all );

		return [ 'community' => $total, 'all' => $all, 'percent' => $percent ];		
	}

	public function totalUsersByCommunity(){
		if( !$this->community || !$this->slug ){
			return;
		}

		$chart = new Crunchbutton_Chart_User();
		$total = $chart->totalUsersByCommunity( $this->slug );
		$all = $chart->totalUsersAll();
		
		$percent = intval( $total * 100 / $all );

		return [ 'community' => $total, 'all' => $all, 'percent' => $percent ];
	}

	public function totalOrdersByCommunity(){
		
		if( !$this->community || !$this->slug ){
			return;
		}

		$chart = new Crunchbutton_Chart_Order();
		$total = $chart->totalOrdersByCommunity( $this->slug );
		$all = $chart->totalOrdersAll();
		
		$percent = intval( $total * 100 / $all );

		return [ 'community' => $total, 'all' => $all, 'percent' => $percent ];
	}

	public function ordersLastWeek(){
		
		if( !$this->community || !$this->slug ){
			return;
		}
		
		$chart = new Crunchbutton_Chart_Order();

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$now->modify( '-1 day' );
		$chart->dayTo = $now->format( 'Y-m-d' );
		$now->modify( '-6 days' );
		$chart->dayFrom = $now->format( 'Y-m-d' );
		$chart->justGetTheData = true;
		$orders = $chart->byDayPerCommunity( false, $this->slug );

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

}