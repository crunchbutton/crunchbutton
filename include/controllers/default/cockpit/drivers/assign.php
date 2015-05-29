<?php

class Controller_drivers_assign extends Crunchbutton_Controller_Account {

	public function init() {

		if ( !c::admin()->permission()->check( [ 'global','drivers-all', 'drivers-assign' ] ) ) {
			return ;
		}

		c::view()->page = 'drivers';

		switch ( c::getPagePiece( 2 ) ){

			case 'driver':
					$this->driver();
				break;

			case 'community':
					$this->community();
				break;

			case 'restaurant':
					$this->restaurant();
				break;

			default:
				c::view()->display( 'drivers/assign/index' );
				break;
		}
	}

	public function loadData(){

		// restaurants
		c::view()->restaurants = Restaurant::q( 'SELECT * FROM restaurant WHERE active = true ORDER BY name ASC' );

		// drivers
		c::view()->drivers = Admin::drivers();

		// communities
		$communities = [];
		$_communities = Crunchbutton_Community::q( 'SELECT * FROM community ORDER BY name ASC' );
		foreach( $_communities as $community ){
			$communities[ $community->id_community ] = $community->name;
		}
		c::view()->communities = $communities;
	}

	public function community(){

		$this->loadData();

		if( c::getPagePiece( 3 ) ){
			$community = Crunchbutton_Community::o( c::getPagePiece( 3 ) );
			$drivers = $community->getDriversOfCommunity();
			$deliveryFor = [];
			foreach( $drivers as $driver ){
				$deliveryFor[ $driver->id_admin ] = true;
			}
			c::view()->restaurants_community = $community->getRestaurants();
			c::view()->drivers_delivery = $deliveryFor;
			c::view()->community = $community;
		}
		c::view()->display( 'drivers/assign/community' );
	}

	public function restaurant(){

		$this->loadData();

		if( c::getPagePiece( 3 ) ){
			$restaurant = Restaurant::o( c::getPagePiece( 3 ) );
			$drivers = $restaurant->drivers();
			$deliveryFor = [];
			foreach( $drivers as $driver ){
				$deliveryFor[ $driver->id_admin ] = true;
			}
			c::view()->drivers_delivery = $deliveryFor;
			c::view()->id_restaurant = $restaurant->id_restaurant;
		}

		c::view()->display( 'drivers/assign/restaurant' );
	}


	public function driver(){

		$this->loadData();

		if( c::getPagePiece( 3 ) ){
			$admin = Admin::o( c::getPagePiece( 3 ) );
			$restaurants = $admin->restaurantsHeDeliveryFor();
			$deliveryFor = [];
			foreach( $restaurants as $restaurant ){
				$deliveryFor[ $restaurant->id_restaurant ] = true;
			}
			$adminCommunities = [];
			$groups = $admin->groups();
			$communities = Crunchbutton_Community::q( 'SELECT * FROM community ORDER BY name ASC' );
			foreach ( $communities as $community ) {
				if( $community->driverDeliveryHere( $admin->id_admin ) ){
					$adminCommunities[ $community->id_community ] = true;
				}
			}
			c::view()->admin_communities = $adminCommunities;
			c::view()->restaurants_delivery = $deliveryFor;
			c::view()->id_admin = $admin->id_admin;
		}

		c::view()->display( 'drivers/assign/driver' );
	}

}