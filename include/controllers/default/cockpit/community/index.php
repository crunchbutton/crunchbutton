<?php

class Controller_community extends Crunchbutton_Controller_Account {
	
	public function init() {

		$slug = c::getPagePiece( 1 );

		if ( !c::admin()->permission()->check( [ 'global','community-all','community-page' ] ) ) {
			return;
		}

		c::view()->page = 'community';

		// Report with the orders from the last 14 days
		if( $slug == 'report' ){
			$this->report();
			exit;
		}

		if( $slug ){

			$permission = "community-communities-{$slug}";

			switch ( c::getPagePiece( 2 ) ) {

				case 'restaurants':

					if ( ( 	( !c::admin()->permission()->check( [ 'global','community-all', $permission ] ) ) ) ||
									( !c::admin()->permission()->check( [ 'global','community-all', 'community-restaurants' ] ) )
						) {
						return;
					}

					c::view()->restaurants = $this->restaurants( $slug );
					c::view()->layout( 'layout/ajax' );
					c::view()->display( 'community/community/restaurants' );
					break;
				
			case 'drivers':
					
					if ( ( 	( !c::admin()->permission()->check( [ 'global','community-all', $permission ] ) ) ) ||
									( !c::admin()->permission()->check( [ 'global','community-all', 'community-restaurants' ] ) )
						) {
						return;
					}

					if ( !c::admin()->permission()->check( [ 'global','community-all', 'community-restaurants' ] ) ) {
						return;
					}
					
					c::view()->drivers = $this->drivers( $slug );
					c::view()->layout( 'layout/ajax' );
					c::view()->display( 'community/community/drivers' );
					break;

				default:

					$permission = "community-communities-{$slug}";
					if ( !c::admin()->permission()->check( [ 'global','community-all', $permission ] ) ) {
						return;
					}

					c::view()->community = $this->basicInfo( $slug );
					c::view()->restaurantsPermissions = ( c::admin()->permission()->check( [ 'global','community-all', 'community-restaurants' ] ) );
					c::view()->driversPermissions = ( c::admin()->permission()->check( [ 'global','community-all', 'community-drivers' ] ) );
					c::view()->display( 'community/community/index' );
					break;
			}

		} else {
			$communities = Restaurant::getCommunities();
			if ( !c::admin()->permission()->check( [ 'global','community-all' ] ) ) {
				$_communities = [];
				foreach ( $communities as $community ) {
					$permission_name = strtolower( $community );
					$permission_name = str_replace( ' ' , '-', $permission_name );
					$permission_name = "community-communities-{$permission_name}";
					if( c::admin()->permission()->check( [ $permission_name ] ) ){
						$_communities[] = $community;
					}
				}
				$communities = $_communities;
			}
			c::view()->communities = $communities;
			
			c::view()->display( 'community/index' );
		}
	}

	public function drivers( $slug ){
		$community = new Restaurant_Communities();
		$community->setSlug( $slug );
		$drivers = $community->getDriversOfCommunity();
		// sort by working
		$_sorted = [];
		foreach( $drivers as $driver ){
			if( $driver->isWorking() ){
				$_sorted[] = $driver;	
			}
		}
		foreach( $drivers as $driver ){
			if( !$driver->isWorking() ){
				$_sorted[] = $driver;	
			}
		}
		return $_sorted;
	}

	public function restaurants( $slug ){
		$community = new Restaurant_Communities();
		$community->setSlug( $slug );
		return $community->restaurants();
	}

	public function basicInfo( $slug ){
		
		$info = [ 'slug' => $slug ];

		$community = new Restaurant_Communities();
		$community->setSlug( $slug );
		
		$info[ 'name' ] = $community->name(); 
		$info[ 'ordersLastWeek' ] = $community->ordersLastWeek();
		$info[ 'newUsersLastWeek' ] = $community->newUsersLastWeek();

		$numbers = [];
		$numbers[] = [ 'title' => 'Orders', 'icon' => 'truck', 'values' => $community->totalOrdersByCommunity() ];
		$numbers[] = [ 'title' => 'Users', 'icon' => 'user', 'values' => $community->totalUsersByCommunity() ];
		$numbers[] = [ 'title' => 'Restaurants', 'icon' => 'food', 'values' => $community->totalRestaurantsByCommunity() ];
		$numbers[] = [ 'title' => 'Drivers', 'icon' => 'road', 'values' => $community->totalDriversByCommunity() ];
		$info[ 'numbers' ] = $numbers;

		return $info;
	}

	public function report(){
		$interval = 14;
		$communities = Restaurant::getCommunities();
		$orders = [];
		$days = [];
		foreach ( $communities as $community ) {
			$orders[ $community ] = Restaurant::getOrdersFromLastDaysByCommunity( $community, $interval );
		}
		$today = new DateTime( $time, new DateTimeZone( 'America/Los_Angeles' ) ); 
		for( $i = 0; $i <= $interval; $i++ ){
			$days[] = $today->format( 'm/d/Y' );
			$today->modify( '-1 day' );
		}
		c::view()->days = $days;
		c::view()->orders = $orders;
		c::view()->layout('layout/csv');
		c::view()->display('community/csv', ['display' => true, 'filter' => false]);
	}

}