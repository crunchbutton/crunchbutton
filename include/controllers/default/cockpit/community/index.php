<?php

class Controller_community extends Crunchbutton_Controller_Account {
	
	public function init() {
		$slug = c::getPagePiece( 1 );

		c::view()->page = 'community';

		if( $slug ){

			switch ( c::getPagePiece( 2 ) ) {

				case 'restaurants':
					c::view()->restaurants = $this->restaurants( $slug );
					c::view()->layout( 'layout/ajax' );
					c::view()->display( 'community/community/restaurants' );
					break;
				
				default:
					c::view()->community = $this->basicInfo( $slug );
					c::view()->display( 'community/community/index' );
					break;
			}

		} else {
			c::view()->communities = Restaurant::getCommunities();
			c::view()->display( 'community/index' );
		}
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


}