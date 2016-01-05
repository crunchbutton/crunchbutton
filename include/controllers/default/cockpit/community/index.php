<?php

class Controller_community extends Crunchbutton_Controller_Account {

	public function init() {

		$id_community = c::getPagePiece( 1 );

		if ( !c::admin()->permission()->check( [ 'global','community-all','community-page' ] ) ) {
			return;
		}

		c::view()->page = 'community';

		// Report with the orders from the last 14 days
		if( $id_community == 'report' ){
			$this->report();
			exit;
		}

		if( $id_community ){

			$community = Crunchbutton_Community::o( $id_community );

			$permission = "community-communities-{$community->slug()}";

			switch ( c::getPagePiece( 2 ) ) {

				case 'restaurants':

					if ( ( 	( !c::admin()->permission()->check( [ 'global','community-all', $permission ] ) ) ) ||
									( !c::admin()->permission()->check( [ 'global','community-all', 'community-restaurants' ] ) )
						) {
						return;
					}

					c::view()->restaurants = $community->getRestaurants();
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

					c::view()->community = $community;
					c::view()->drivers = $this->drivers( $id_community );
					c::view()->layout( 'layout/ajax' );
					c::view()->display( 'community/community/drivers' );
					break;

			case 'drivers-sms':
				c::view()->community = $community;
				c::view()->drivers = $this->drivers( $id_community );
				c::view()->layout( 'layout/ajax' );
				c::view()->display( 'community/community/drivers-sms' );
				break;

				default:

					$permission = "community-communities-{$community->slug()}";
					if ( !c::admin()->permission()->check( [ 'global','community-all', $permission ] ) ) {
						return;
					}

					c::view()->community = $this->basicInfo( $id_community );
					c::view()->restaurantsPermissions = ( c::admin()->permission()->check( [ 'global','community-all', 'community-restaurants' ] ) );
					c::view()->driversPermissions = ( c::admin()->permission()->check( [ 'global','community-all', 'community-drivers' ] ) );
					c::view()->display( 'community/community/index' );
					break;
			}

		} else {
			$communities = Crunchbutton_Community::q( 'SELECT * FROM community WHERE active = true ORDER BY name ASC' );
			c::view()->communities = $communities;
			c::view()->display( 'community/index' );
		}
	}

	public function drivers( $id_community ){
		$community = Crunchbutton_Community::o( $id_community );
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

	public function basicInfo( $id_community ){

		$community = Crunchbutton_Community::o( $id_community );

		$info[ 'community' ] = $community;

		$info[ 'name' ] = $community->name;
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
		$communities = Crunchbutton_Community::q( 'SELECT * FROM community WHERE active = true ORDER BY name ASC' );
		$orders = [];
		$days = [];
		foreach ( $communities as $community ) {
			$orders[ $community->name ] = $community->getOrdersFromLastDaysByCommunity( $interval );
		}
		$today = new DateTime( $time, new DateTimeZone(c::config()->timezone) );
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
