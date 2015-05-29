<?php

class Controller_home_curation extends Crunchbutton_Controller_Account {

	public function init() {
		header('Location: https://github.com/crunchbutton/crunchbutton/issues/2605');
		exit;

		if (!c::admin()->permission()->check(['global', 'curation'])) {
			return ;
		}

		$orderByCategory = ( c::getPagePiece(2) == 'category' );
		$showInactive = ( $_GET[ 'showInactive' ] ) ? $_GET[ 'showInactive' ] : 0;

		$totalFoodOrdered = Crunchbutton_Order_Dish::totalDishesOrdered();

		$data = [];
		$restaurants = Restaurant::q('SELECT * FROM restaurant WHERE active = true ORDER BY name ASC');

		// It means the user can see the curation of all restaurants
		if( c::admin()->permission()->check( [ 'global', 'restaurants-all', 'restaurants-crud'] ) ){
			$restaurants = Restaurant::q('SELECT * FROM restaurant WHERE active = true ORDER BY name ASC');
		} else {
			$_restaurants_id = c::admin()->getRestaurantsUserHasCurationPermission();
			$in = join( ',', $_restaurants_id );
			$restaurants = Restaurant::q("SELECT * FROM restaurant WHERE active = true AND id_restaurant IN( {$in} ) ORDER BY name ASC");
		}

		foreach( $restaurants as $restaurant ){
			$data[ $restaurant->id_restaurant ] = [];
			$data[ $restaurant->id_restaurant ][ 'Name' ] = $restaurant->name;
			$data[ $restaurant->id_restaurant ][ 'Food' ] = $restaurant->foodReport( $orderByCategory, ( $showInactive == 1 ) );
		}

	c::view()->totalFoodOrdered = $totalFoodOrdered;
	c::view()->data = $data;
	c::view()->display('home/curation');

	}
}