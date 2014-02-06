<?php

class Controller_drivers_assign extends Crunchbutton_Controller_Account {
	
	public function init() {

		if ( !c::admin()->permission()->check( [ 'global' ] ) ){
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
		c::view()->restaurants = Restaurant::q( 'SELECT * FROM restaurant WHERE active = 1 ORDER BY name ASC' );

		// drivers
		c::view()->drivers = Admin::q( 'SELECT DISTINCT( a.id_admin ) id, a.* FROM admin a INNER JOIN admin_notification an ON an.id_admin = a.id_admin AND an.active = 1 ORDER BY name ASC' );	

		// communities
		$communities = [];
		$_communities = Restaurant::getCommunities();
		foreach( $_communities as $community ){
			$communities[ Crunchbutton_Group::driverGroupOfCommunity( $community ) ] = $community;
		}
		c::view()->communities = $communities;
	}

	public function community(){

		$this->loadData();

		if( c::getPagePiece( 3 ) ){
			$community = c::getPagePiece( 3 );
			$group = Crunchbutton_Group::getDeliveryGroupByCommunity( $community );
			$drivers = Crunchbutton_Admin::q( "SELECT a.* FROM admin a INNER JOIN admin_group ag ON ag.id_admin = a.id_admin AND ag.id_group = {$group->id_group}" );
			$deliveryFor = [];
			foreach( $drivers as $driver ){
				$deliveryFor[ $driver->id_admin ] = true;
			}
			c::view()->restaurants_community = Restaurant::getRestaurantsByCommunity( Crunchbutton_Group::getRestaurantCommunityName( $community ) );
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
			foreach ( $groups as $group ) {
				$adminCommunities[ $group->name ] = true;
			}
			c::view()->admin_communities = $adminCommunities;
			c::view()->restaurants_delivery = $deliveryFor;
			c::view()->id_admin = $admin->id_admin;
		}

		c::view()->display( 'drivers/assign/driver' );
	}

}